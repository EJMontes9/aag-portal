<?php

namespace App\Livewire;

use App\Models\Media;
use App\Services\MediaService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MediaPicker extends Component
{
    use WithFileUploads, WithPagination;

    public bool   $open        = false;
    public string $targetField = 'image';
    public string $search      = '';
    public string $filterType  = 'image';
    public        $uploadFile  = null;

    /**
     * SEGURIDAD -- Livewire ejecuta boot() en CADA peticion al componente,
     * antes que cualquier metodo de accion. Autorizar aqui (y no metodo por
     * metodo) cierra la clase entera: ningun metodo que se anada en el futuro
     * puede quedar sin proteger por olvido.
     *
     * Este componente no tenia ninguna comprobacion de rol. No era alcanzable
     * por un anonimo porque solo se monta dentro del editor visual, que si
     * exige autenticacion, pero dependia de esa circunstancia y no de un
     * control propio.
     */
    public function boot(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin', 'editor', 'publisher'])) {
            abort(403, 'No autorizado');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Open / Close
    // ──────────────────────────────────────────────────────────────────────────

    /** Escucha el evento Livewire 'open-media-picker' despachado desde VisualEditor */
    #[On('open-media-picker')]
    public function openPicker(string $field = 'image', string $type = 'image'): void
    {
        $this->targetField  = $field;
        $this->filterType   = $type;
        $this->search       = '';
        $this->resetPage();
        $this->open         = true;
    }

    public function close(): void
    {
        $this->open       = false;
        $this->uploadFile = null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Select
    // ──────────────────────────────────────────────────────────────────────────

    public function selectMedia(int $mediaId): void
    {
        $media = Media::find($mediaId);
        if (! $media) return;

        // Notifica a VisualEditor (u otro componente padre) con el campo y el path
        $this->dispatch('media-selected',
            field: $this->targetField,
            path:  $media->path,
            url:   $media->url,
        );

        $this->close();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Upload dentro del picker
    // ──────────────────────────────────────────────────────────────────────────

    public function uploadMedia(): void
    {
        // SEGURIDAD -- La regla "mimes" no es decorativa: Laravel solo aplica
        // su bloqueo interno de archivos PHP (shouldBlockPhpUpload) cuando hay
        // una regla mimes/mimetypes presente. Sin ella, la validacion anterior
        // ("required|file|max:20480") dejaba pasar cualquier cosa.
        //
        // "mimetypes" comprueba el contenido real del archivo; "mimes" la
        // extension. Se usan las dos: la primera evita el renombrado, la
        // segunda activa el bloqueo interno de Laravel.
        //
        // Esta validacion se solapa a proposito con la allowlist de
        // MediaService, que es la barrera definitiva. Aqui sirve para dar un
        // mensaje de error decente al usuario antes de llegar alli.
        $this->validate([
            'uploadFile' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,webm',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp,application/pdf,'
                    . 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,'
                    . 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,'
                    . 'application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,'
                    . 'video/mp4,video/webm',
            ],
        ], [
            'uploadFile.required'  => 'Selecciona un archivo.',
            'uploadFile.max'       => 'El archivo no puede superar 10 MB.',
            'uploadFile.mimes'     => 'Formato no permitido. Se aceptan imagenes, PDF, documentos de Office y video MP4.',
            'uploadFile.mimetypes' => 'El contenido del archivo no corresponde a un formato permitido.',
        ]);

        try {
            $media = MediaService::upload($this->uploadFile);
            $this->uploadFile = null;
            $this->resetPage();

            // Auto-seleccionar el recién subido
            $this->selectMedia($media->id);
        } catch (\Throwable $e) {
            // No se expone $e->getMessage() al usuario: puede incluir rutas del
            // servidor. El detalle va al log; el usuario ve algo generico.
            report($e);
            $this->addError('uploadFile', 'No se pudo subir el archivo. Verifica que el formato sea válido.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────────────────────────────────

    public function render()
    {
        $mediaItems = Media::when($this->search, fn ($q) =>
                          $q->where('name', 'like', "%{$this->search}%")
                             ->orWhere('alt_text', 'like', "%{$this->search}%")
                      )
                      ->when($this->filterType, fn ($q) =>
                          $q->where('type', $this->filterType)
                      )
                      ->latest()
                      ->paginate(24);

        return view('livewire.media-picker', compact('mediaItems'));
    }
}
