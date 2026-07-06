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
        $this->validate([
            'uploadFile' => 'required|file|max:20480',
        ], [
            'uploadFile.required' => 'Selecciona un archivo.',
            'uploadFile.max'      => 'El archivo no puede superar 20 MB.',
        ]);

        try {
            $media = MediaService::upload($this->uploadFile);
            $this->uploadFile = null;
            $this->resetPage();

            // Auto-seleccionar el recién subido
            $this->selectMedia($media->id);
        } catch (\Throwable $e) {
            $this->addError('uploadFile', 'Error al subir: ' . $e->getMessage());
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
