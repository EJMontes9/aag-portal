<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    const MAX_WIDTH  = 1920;
    const MAX_HEIGHT = 1200;
    const QUALITY    = 85;

    /**
     * Tipos permitidos, indexados por MIME REAL (leído del contenido del
     * archivo, no de lo que declare el cliente) y con la extensión que se le
     * asignará al guardarlo.
     *
     * SEGURIDAD -- Esta lista es la barrera principal contra la ejecución
     * remota de código. Antes no existía: se guardaba el archivo con la
     * extensión que enviaba el cliente ($file->getClientOriginalExtension()),
     * de modo que un .php subido desde la galería de medios quedaba bajo
     * storage/app/public y Apache lo ejecutaba a través del symlink /storage.
     *
     * Reglas al tocar esta lista:
     *   - La extensión SIEMPRE se deriva de aquí, nunca del nombre original.
     *   - Nada de SVG: admite <script> y se sirve en nuestro propio origen,
     *     así que es XSS almacenado contra quien lo abra (normalmente un
     *     administrador). Si algún día hace falta, hay que sanearlo antes con
     *     una librería dedicada y servirlo como adjunto.
     *   - Nada de HTML, XML ni nada interpretable por el navegador.
     */
    const TIPOS_PERMITIDOS = [
        // Imágenes
        'image/jpeg'  => 'jpg',
        'image/png'   => 'png',
        'image/gif'   => 'gif',
        'image/webp'  => 'webp',
        // Documentos
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        // Video
        'video/mp4'   => 'mp4',
        'video/webm'  => 'webm',
    ];

    /**
     * Extensiones que jamás deben escribirse en disco, pase lo que pase.
     * Es una segunda red por si alguien amplía TIPOS_PERMITIDOS sin pensar.
     */
    const EXTENSIONES_PROHIBIDAS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'pht', 'phps', 'phar',
        'htaccess', 'htpasswd', 'ini', 'conf',
        'svg', 'svgz', 'html', 'htm', 'xhtml', 'xml', 'xsl',
        'js', 'mjs', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'exe', 'dll', 'jar',
    ];

    /**
     * Valida el archivo contra la allowlist y devuelve la extensión segura.
     *
     * @throws \RuntimeException si el tipo no está permitido.
     */
    public static function extensionSegura(UploadedFile $file): string
    {
        // getMimeType() lee el CONTENIDO del archivo (finfo). getClientMimeType()
        // es solo lo que dice la cabecera del multipart, que el atacante controla,
        // así que no sirve para decidir.
        $mime = $file->getMimeType();

        if (! $mime || ! isset(self::TIPOS_PERMITIDOS[$mime])) {
            throw new \RuntimeException(
                'Tipo de archivo no permitido' . ($mime ? " ({$mime})" : '') . '.'
            );
        }

        $ext = self::TIPOS_PERMITIDOS[$mime];

        if (in_array(strtolower($ext), self::EXTENSIONES_PROHIBIDAS, true)) {
            throw new \RuntimeException('Tipo de archivo no permitido.');
        }

        return $ext;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Upload entry point (UploadedFile o TemporaryUploadedFile)
    // ──────────────────────────────────────────────────────────────────────────

    public static function upload(UploadedFile $file, ?string $folder = null): Media
    {
        // Se valida ANTES de tocar el disco: si el tipo no está permitido, la
        // excepción sube y no se escribe nada.
        $ext = self::extensionSegura($file);

        $mime   = $file->getMimeType();
        $type   = self::detectType($mime);
        $folder = $folder ?? ('media/' . now()->format('Y/m'));

        return $type === 'image'
            ? self::processImage($file, $folder)
            : self::storeFile($file, $folder, $type, $ext);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Re-process an already-stored file (path on 'public' disk)
    // Used when Filament FileUpload already saved the file before we can hook in
    // ──────────────────────────────────────────────────────────────────────────

    public static function processFromStoredPath(string $storedPath): Media
    {
        $fullPath = Storage::disk('public')->path($storedPath);
        $mime     = mime_content_type($fullPath) ?: 'application/octet-stream';

        // SEGURIDAD -- Aquí el archivo YA está escrito en disco: Filament lo
        // guarda antes de que podamos intervenir. Así que si el tipo no está
        // permitido hay que BORRARLO, no limitarse a rechazarlo; si no, queda
        // accesible por URL aunque no se registre en la tabla media.
        // Este era el caso del SVG: Image::decodePath fallaba, el catch de
        // MediaResource solo escribía un aviso en el log, y el archivo quedaba
        // huérfano pero públicamente accesible.
        if (! isset(self::TIPOS_PERMITIDOS[$mime])) {
            Storage::disk('public')->delete($storedPath);
            throw new \RuntimeException("Tipo de archivo no permitido ({$mime}). El archivo fue descartado.");
        }

        $type = self::detectType($mime);

        if ($type === 'image') {
            $img = Image::decodePath($fullPath);

            if ($img->width() > self::MAX_WIDTH) {
                $img->scaleDown(width: self::MAX_WIDTH);
            }

            $baseName = Str::slug(pathinfo($storedPath, PATHINFO_FILENAME));
            $webpName = $baseName . '-' . substr(uniqid(), -6) . '.webp';
            $webpPath = dirname($storedPath) . '/' . $webpName;

            $encoded = $img->encode(new WebpEncoder(quality: self::QUALITY));
            Storage::disk('public')->put($webpPath, (string) $encoded);

            // Eliminar original si el nombre cambió
            if ($webpPath !== $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            return Media::create([
                'name'      => $baseName . '.webp',
                'file_name' => $webpName,
                'disk'      => 'public',
                'path'      => $webpPath,
                'mime_type' => 'image/webp',
                'extension' => 'webp',
                'size'      => Storage::disk('public')->size($webpPath),
                'width'     => $img->width(),
                'height'    => $img->height(),
                'type'      => 'image',
            ]);
        }

        // No es imagen (documento o video ya validado contra la allowlist).
        // La extensión se toma de la allowlist, no del nombre en disco: si
        // Filament lo guardó con una extensión engañosa, se renombra.
        $extSegura = self::TIPOS_PERMITIDOS[$mime];
        $extActual = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));

        if ($extActual !== $extSegura) {
            $nuevoPath = preg_replace('/\.[^.]*$/', '', $storedPath) . '.' . $extSegura;
            Storage::disk('public')->move($storedPath, $nuevoPath);
            $storedPath = $nuevoPath;
        }

        return Media::create([
            'name'      => basename($storedPath),
            'file_name' => basename($storedPath),
            'disk'      => 'public',
            'path'      => $storedPath,
            'mime_type' => $mime,
            'extension' => $extSegura,
            'size'      => Storage::disk('public')->size($storedPath),
            'type'      => $type,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internals
    // ──────────────────────────────────────────────────────────────────────────

    protected static function processImage(UploadedFile $file, string $folder): Media
    {
        $img = Image::decodePath($file->getPathname());

        if ($img->width() > self::MAX_WIDTH) {
            $img->scaleDown(width: self::MAX_WIDTH);
        }

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $baseName . '-' . substr(uniqid(), -6) . '.webp';
        $path     = $folder . '/' . $fileName;

        $encoded = $img->encode(new WebpEncoder(quality: self::QUALITY));
        Storage::disk('public')->put($path, (string) $encoded);

        return Media::create([
            'name'      => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'disk'      => 'public',
            'path'      => $path,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size'      => Storage::disk('public')->size($path),
            'width'     => $img->width(),
            'height'    => $img->height(),
            'type'      => 'image',
        ]);
    }

    protected static function storeFile(UploadedFile $file, string $folder, string $type, ?string $ext = null): Media
    {
        // La extensión SIEMPRE sale de la allowlist, derivada del MIME real.
        // Nunca de $file->getClientOriginalExtension(), que la controla quien
        // sube el archivo: ese era el origen de la ejecución remota de código.
        $ext = $ext ?? self::extensionSegura($file);

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        // Str::slug puede devolver cadena vacía (nombre solo con símbolos o en
        // un alfabeto no latino); sin esto el archivo se llamaría "-a1b2c3.pdf".
        if ($baseName === '') {
            $baseName = 'archivo';
        }
        $baseName = Str::limit($baseName, 80, '');

        $fileName = $baseName . '-' . substr(uniqid(), -6) . '.' . $ext;
        $path     = $file->storeAs($folder, $fileName, 'public');

        return Media::create([
            // El nombre original solo se guarda para mostrarlo; se recorta para
            // no admitir cadenas enormes en la BD.
            'name'      => Str::limit($file->getClientOriginalName(), 180, ''),
            'file_name' => $fileName,
            'disk'      => 'public',
            'path'      => $path,
            'mime_type' => $file->getMimeType(),
            'extension' => $ext,
            'size'      => $file->getSize(),
            'type'      => $type,
        ]);
    }

    public static function detectType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])) return 'document';
        return 'other';
    }
}
