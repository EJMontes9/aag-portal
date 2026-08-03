<?php

namespace App\Livewire;

use App\Blocks\BlockRegistry;
use App\Models\Page;
use App\Models\PageBlock;
use App\Services\MediaService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class VisualEditor extends Component
{
    use WithFileUploads;

    public Page $page;
    public ?int $editingBlockId = null;
    public ?array $editingBlockSettings = null;
    public ?string $editingBlockType = null;
    public bool $panelOpen = false;

    /** Imagen temporal para upload de slide individual. */
    public $slideImage = null;

    /** Imagen temporal para upload de imagen de bloque (text-image, hero, etc.) */
    public $blockImage = null;

    public function mount(Page $page): void
    {
        $this->authorizeEditor();
        $this->page = $page->load('blocks');
    }

    protected function authorizeEditor(): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['super_admin', 'admin', 'editor', 'publisher'])) {
            abort(403, 'No autorizado');
        }
    }

    public function openBlock(int $blockId): void
    {
        $this->authorizeEditor();
        $block = $this->page->blocks->firstWhere('id', $blockId);
        if (! $block) return;
        $this->editingBlockId = $block->id;
        $this->editingBlockType = $block->type;
        $this->editingBlockSettings = $block->settings ?? [];
        $this->panelOpen = true;
    }

    public function closePanel(): void
    {
        $this->panelOpen = false;
        $this->editingBlockId = null;
        $this->editingBlockSettings = null;
        $this->editingBlockType = null;
    }

    public function saveBlock(): void
    {
        $this->authorizeEditor();
        if (! $this->editingBlockId) return;

        // SEGURIDAD -- Se busca el bloque DENTRO de la página que se está
        // editando, no globalmente. $editingBlockId es una propiedad pública y
        // en Livewire el cliente puede fijar su valor en la petición, así que
        // un PageBlock::find() suelto permitía escribir en bloques de
        // cualquier otra página (y además invalidaba la caché de la página
        // equivocada, dejando el cambio invisible y el log de auditoría
        // apuntando al sitio erróneo).
        //
        // Es el mismo patrón que ya usaban openBlock, moveUp, moveDown,
        // toggleVisibility y deleteBlock; saveBlock era la excepción.
        $block = $this->page->blocks()->whereKey($this->editingBlockId)->first();
        if (! $block) return;

        $block->update(['settings' => $this->editingBlockSettings ?? []]);
        Page::clearCache($this->page->key);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($block)
            ->withProperties(['page' => $this->page->title, 'type' => $block->type])
            ->event('updated')
            ->log("Bloque editado: {$block->type} — página \"{$this->page->title}\"");

        $this->page = $this->page->fresh()->load('blocks');
        $this->dispatch('block-saved');
    }

    public function moveUp(int $blockId): void
    {
        $this->authorizeEditor();
        $blocks = $this->page->blocks->sortBy('sort_order')->values();
        $idx = $blocks->search(fn ($b) => $b->id === $blockId);
        if ($idx === false || $idx === 0) return;

        $newOrder = $blocks->pluck('id')->toArray();
        [$newOrder[$idx - 1], $newOrder[$idx]] = [$newOrder[$idx], $newOrder[$idx - 1]];
        $this->reorderBlocks($newOrder);
    }

    public function moveDown(int $blockId): void
    {
        $this->authorizeEditor();
        $blocks = $this->page->blocks->sortBy('sort_order')->values();
        $idx = $blocks->search(fn ($b) => $b->id === $blockId);
        if ($idx === false || $idx === $blocks->count() - 1) return;

        $newOrder = $blocks->pluck('id')->toArray();
        [$newOrder[$idx], $newOrder[$idx + 1]] = [$newOrder[$idx + 1], $newOrder[$idx]];
        $this->reorderBlocks($newOrder);
    }

    /**
     * Reordena bloques según una lista de IDs en el nuevo orden.
     * Llamado desde JS (Sortable) tras un drag & drop, o desde moveUp/moveDown.
     */
    public function reorderBlocks(array $orderedIds): void
    {
        $this->authorizeEditor();

        // Validar que todos los IDs pertenezcan a esta página
        $valid = $this->page->blocks()->whereIn('id', $orderedIds)->pluck('id')->all();
        if (count($valid) !== count($orderedIds)) {
            throw new \RuntimeException('Lista de bloques inválida');
        }

        \DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $idx => $id) {
                \App\Models\PageBlock::where('id', $id)->update(['sort_order' => $idx]);
            }
        });

        Page::clearCache($this->page->key);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['page' => $this->page->title, 'order' => $orderedIds])
            ->event('updated')
            ->log("Bloques reordenados — página \"{$this->page->title}\"");

        $this->page = $this->page->fresh()->load('blocks');
    }

    public function toggleVisibility(int $blockId): void
    {
        $this->authorizeEditor();
        $block = $this->page->blocks->firstWhere('id', $blockId);
        if (! $block) return;
        $newState = ! $block->is_active;
        $block->update(['is_active' => $newState]);
        Page::clearCache($this->page->key);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($block)
            ->withProperties(['page' => $this->page->title, 'type' => $block->type, 'visible' => $newState])
            ->event('updated')
            ->log(($newState ? 'Bloque mostrado' : 'Bloque ocultado') . ": {$block->type} — página \"{$this->page->title}\"");

        $this->page = $this->page->fresh()->load('blocks');
    }

    public function deleteBlock(int $blockId): void
    {
        $this->authorizeEditor();
        $block = $this->page->blocks->firstWhere('id', $blockId);
        if (! $block) return;

        activity()
            ->causedBy(auth()->user())
            ->performedOn($block)
            ->withProperties(['page' => $this->page->title, 'type' => $block->type])
            ->event('deleted')
            ->log("Bloque eliminado: {$block->type} — página \"{$this->page->title}\"");

        $block->delete();
        Page::clearCache($this->page->key);
        $this->page = $this->page->fresh()->load('blocks');
        if ($this->editingBlockId === $blockId) {
            $this->closePanel();
        }
    }

    public function addBlock(string $type): void
    {
        $this->authorizeEditor();

        $blockTypeClass = collect(BlockRegistry::types())
            ->first(fn ($c) => $c::key() === $type);
        $defaults = $blockTypeClass ? $blockTypeClass::defaults() : [];

        $maxOrder = (int) $this->page->blocks()->max('sort_order');
        $newBlock = $this->page->blocks()->create([
            'type' => $type,
            'settings' => $defaults,
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);
        Page::clearCache($this->page->key);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($newBlock)
            ->withProperties(['page' => $this->page->title, 'type' => $type])
            ->event('created')
            ->log("Bloque agregado: {$type} — página \"{$this->page->title}\"");

        $this->page = $this->page->fresh()->load('blocks');
        $this->dispatch('block-added', blockId: $newBlock->id);
        $this->dispatch('editor-toast', type: 'success', message: 'Bloque agregado');
    }

    public function getBlockTypesProperty(): array
    {
        return collect(BlockRegistry::types())->map(fn ($cls) => [
            'key' => $cls::key(),
            'label' => $cls::label(),
        ])->all();
    }

    /**
     * Agrega un item a un repeater dentro de editingBlockSettings.
     * $key es el nombre del repeater (ej: 'slides', 'items', 'links').
     * $defaults son los valores iniciales del item nuevo.
     */
    public function addRepeaterItem(string $key, array $defaults = []): void
    {
        $this->authorizeEditor();
        $items = $this->editingBlockSettings[$key] ?? [];
        $items[] = $defaults;
        $this->editingBlockSettings[$key] = $items;
    }

    public function removeRepeaterItem(string $key, int $index): void
    {
        $this->authorizeEditor();
        $items = $this->editingBlockSettings[$key] ?? [];
        if (! isset($items[$index])) return;
        array_splice($items, $index, 1);
        $this->editingBlockSettings[$key] = $items;
    }

    public function moveRepeaterItem(string $key, int $index, int $direction): void
    {
        $this->authorizeEditor();
        $items = $this->editingBlockSettings[$key] ?? [];
        $newIdx = $index + $direction;
        if (! isset($items[$index]) || ! isset($items[$newIdx])) return;
        [$items[$index], $items[$newIdx]] = [$items[$newIdx], $items[$index]];
        $this->editingBlockSettings[$key] = array_values($items);
    }

    /**
     * Sube una imagen y la asigna a settings[$repeaterKey][$index][$field].
     * Usa el property público $slideImage (Livewire FileUpload).
     */
    public function uploadSlideImage(string $repeaterKey, int $index, string $field = 'image', string $directory = 'banners'): void
    {
        $this->authorizeEditor();

        // SEGURIDAD -- La regla "image" de Laravel ACEPTA SVG, y aquí el
        // archivo se guardaba tal cual, sin reprocesar. Un SVG puede contener
        // <script> y se sirve desde nuestro propio dominio, así que ejecutaba
        // JavaScript con la sesión de quien lo abriera (normalmente un
        // administrador). Se sustituye por una lista explícita de formatos
        // rasterizados.
        $this->validate([
            'slideImage' => [
                'required',
                'image',
                'max:4096',
                'mimes:jpg,jpeg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'slideImage.image'     => 'El archivo debe ser una imagen.',
            'slideImage.max'       => 'La imagen debe pesar menos de 4 MB.',
            'slideImage.mimes'     => 'Formato no permitido. Usa JPG, PNG, GIF o WebP.',
            'slideImage.mimetypes' => 'El contenido del archivo no corresponde a una imagen válida.',
        ]);

        if (! $this->slideImage) return;

        // El directorio venía del cliente. Flysystem ya bloquea el path
        // traversal, pero se restringe a una lista conocida para que no se
        // puedan sembrar carpetas arbitrarias en el disco público.
        $directoriosPermitidos = ['banners', 'bloques', 'media'];
        if (! in_array($directory, $directoriosPermitidos, true)) {
            $directory = 'bloques';
        }

        // Se pasa por MediaService para que re-codifique a WebP: además de
        // pesar menos, el reprocesado destruye cualquier carga útil incrustada
        // en los metadatos de la imagen (polyglots).
        try {
            $media = MediaService::upload($this->slideImage, $directory);
            $path  = $media->path;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('slideImage', 'No se pudo procesar la imagen.');
            return;
        }

        $items = $this->editingBlockSettings[$repeaterKey] ?? [];
        if (! isset($items[$index])) return;
        $items[$index][$field] = $path;
        $this->editingBlockSettings[$repeaterKey] = $items;
        $this->slideImage = null;

        $this->dispatch('editor-toast', type: 'success', message: 'Imagen cargada');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Media Library integration
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Sube $blockImage a la media library, aplica compresión WebP y
     * guarda el path en editingBlockSettings[$field].
     */
    public function uploadBlockImage(string $field = 'image'): void
    {
        $this->authorizeEditor();

        // Mismo criterio que uploadSlideImage: "image" a secas admite SVG.
        $this->validate([
            'blockImage' => [
                'required',
                'image',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp',
                'mimetypes:image/jpeg,image/png,image/gif,image/webp',
            ],
        ], [
            'blockImage.image'     => 'El archivo debe ser una imagen.',
            'blockImage.max'       => 'La imagen no puede superar 10 MB.',
            'blockImage.mimes'     => 'Formato no permitido. Usa JPG, PNG, GIF o WebP.',
            'blockImage.mimetypes' => 'El contenido del archivo no corresponde a una imagen válida.',
        ]);

        if (! $this->blockImage) return;

        try {
            $media = MediaService::upload($this->blockImage);
            $this->editingBlockSettings[$field] = $media->path;
            $this->blockImage = null;
            $this->dispatch('editor-toast', type: 'success', message: 'Imagen subida y comprimida a WebP');
        } catch (\Throwable $e) {
            // Sin $e->getMessage() al usuario: puede filtrar rutas del servidor.
            report($e);
            $this->addError('blockImage', 'No se pudo procesar la imagen.');
        }
    }

    /**
     * Elimina el valor del campo de imagen en el bloque que se está editando.
     */
    public function clearBlockImage(string $field = 'image'): void
    {
        $this->authorizeEditor();
        $this->editingBlockSettings[$field] = null;
    }

    /**
     * Recibe la selección del MediaPicker modal.
     * El MediaPicker despacha 'media-selected' con {field, path}.
     */
    #[On('media-selected')]
    public function onMediaSelected(string $field, string $path, string $url = ''): void
    {
        // Soporta rutas anidadas: 'cards.0.image', 'slides.2.image', etc.
        if (substr_count($field, '.') === 2) {
            [$key, $index, $subkey] = explode('.', $field, 3);
            $this->editingBlockSettings[$key][(int) $index][$subkey] = $path;
        } else {
            $this->editingBlockSettings[$field] = $path;
        }
        $this->dispatch('editor-toast', type: 'success', message: 'Imagen seleccionada de la galería');
    }

    public function clearSlideImage(string $repeaterKey, int $index, string $field = 'image'): void
    {
        $this->authorizeEditor();
        $items = $this->editingBlockSettings[$repeaterKey] ?? [];
        if (! isset($items[$index])) return;
        $items[$index][$field] = null;
        $this->editingBlockSettings[$repeaterKey] = $items;
    }

    public function render()
    {
        return view('livewire.visual-editor', [
            'orderedBlocks' => $this->page->blocks->sortBy('sort_order')->values(),
        ])->layout('layouts.editor', [
            'title' => 'Editor visual · '.$this->page->title,
        ]);
    }
}
