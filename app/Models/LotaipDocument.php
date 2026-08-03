<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class LotaipDocument extends Model
{
    /** El archivo está subido a este hosting, en el disco público. */
    public const SOURCE_LOCAL = 'local';

    /** El archivo vive en el subdominio de documentos, al que se sube por FTP. */
    public const SOURCE_EXTERNAL = 'external';

    protected $fillable = [
        'month_id', 'source', 'title', 'literal', 'file_path', 'extension',
        'file_size', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (! $m->file_path) {
                return;
            }

            // La extensión se deduce de la ruta en ambos casos, quitando antes
            // la query string por si la URL externa trae parámetros.
            $sinQuery = strtok($m->file_path, '?');
            $m->extension = strtolower(pathinfo($sinQuery, PATHINFO_EXTENSION));

            // El tamaño solo se puede leer de los archivos locales. En los
            // externos se deja como lo haya escrito el administrador (o vacío):
            // consultar el subdominio en cada guardado haría depender el panel
            // de que ese servidor responda.
            if ($m->isLocal()) {
                $absPath = Storage::disk('public')->path($m->file_path);
                if (file_exists($absPath) && empty($m->file_size)) {
                    $m->file_size = filesize($absPath);
                }
            }
        });

        $bust = fn () => Cache::forget('transparency_tree');
        static::saved($bust);
        static::deleted($bust);
    }

    public function isLocal(): bool
    {
        return ($this->source ?? self::SOURCE_LOCAL) === self::SOURCE_LOCAL;
    }

    public function isExternal(): bool
    {
        return ! $this->isLocal();
    }

    /**
     * URL base del subdominio de documentos, configurable desde el panel.
     * Se normaliza sin barra final para poder concatenar sin duplicarla.
     */
    public static function baseUrlExterna(): string
    {
        $url = trim((string) settings('documents_base_url', ''));

        return $url !== '' ? rtrim($url, '/') : '';
    }

    public function month(): BelongsTo
    {
        return $this->belongsTo(LotaipMonth::class, 'month_id');
    }

    /**
     * URL pública del documento.
     *
     * Resuelve en este orden, pensado para no romper nada de lo ya publicado:
     *
     *   1. Si file_path ya es una URL absoluta, se devuelve TAL CUAL. Es el
     *      caso de los documentos históricos, cuyos enlaces están publicados
     *      y no deben cambiar aunque se modifique el subdominio configurado.
     *   2. Si el documento es externo, se compone con la URL base del panel.
     *   3. Si es local, sale del disco público como siempre.
     *
     * Solo se admiten esquemas http y https: un administrador podría pegar por
     * error (o con mala intención) un "javascript:..." que se ejecutaría al
     * pulsar el enlace.
     */
    public function getUrlAttribute(): string
    {
        $ruta = trim((string) $this->file_path);

        if ($ruta === '') {
            return '';
        }

        // Protocolo relativo ("//dominio/archivo.pdf")
        if (str_starts_with($ruta, '//')) {
            return 'https:' . $ruta;
        }

        // 1. Cualquier cosa con esquema ("algo:...").
        //    Se comprueba SIN exigir "//" a propósito: "javascript:alert(1)" no
        //    lleva barras, y si solo se buscara "://" pasaría por ruta relativa
        //    y se acabaría concatenando al subdominio como un enlace roto.
        //    Una ruta de archivo legítima no empieza por "esquema:".
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $ruta)) {
            return $this->esEsquemaSeguro($ruta) ? $ruta : '';
        }

        // 2. Externo: se compone con la base configurada
        if ($this->isExternal()) {
            $base = self::baseUrlExterna();

            if ($base === '') {
                // Sin base configurada no se puede construir el enlace. Se
                // devuelve vacío en vez de una URL rota, y la vista lo trata.
                return '';
            }

            // rawurlencode por segmento: respeta las barras de la ruta pero
            // escapa espacios y acentos, frecuentes en los nombres de los
            // archivos que se suben por FTP.
            $segmentos = array_map('rawurlencode', explode('/', ltrim($ruta, '/')));

            return $base . '/' . implode('/', $segmentos);
        }

        // 3. Local
        return Storage::disk('public')->url($ruta);
    }

    protected function esEsquemaSeguro(string $url): bool
    {
        $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($esquema, ['http', 'https'], true);
    }

    /**
     * ¿El enlace se puede construir? La vista lo usa para no pintar un enlace
     * roto cuando falta configurar la URL base del subdominio.
     */
    public function getTieneUrlAttribute(): bool
    {
        return $this->url !== '';
    }

    public function getSizeHumanAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) return '';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        return match ($this->extension) {
            'pdf' => 'fa-file-pdf',
            'csv', 'xlsx', 'xls' => 'fa-file-csv',
            'doc', 'docx' => 'fa-file-word',
            'jpg', 'jpeg', 'png' => 'fa-file-image',
            default => 'fa-file',
        };
    }
}
