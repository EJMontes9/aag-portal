<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use App\Models\PageBlock;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth|string|null
    {
        return \Filament\Support\Enums\MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('visual_editor')
                ->label('Editor visual')
                ->icon('heroicon-o-paint-brush')
                ->color('success')
                ->url(fn () => url('/admin/visual-editor/'.$this->record->id)),
            Actions\Action::make('view_public')
                ->label('Ver publico')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => $this->record->key === 'home' ? url('/') : url('/'.$this->record->slug))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()->visible(fn () => $this->record->key !== 'home'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Leer los PageBlock y convertir al formato que entiende Filament Builder.
        $record = $this->getRecord();
        $data['blocks_builder'] = $record->blocks->sortBy('sort_order')->map(function (PageBlock $b) {
            return [
                'type' => $b->type,
                'data' => array_merge(['__is_active' => $b->is_active, '__block_id' => $b->id], $b->settings ?? []),
            ];
        })->values()->toArray();
        return $data;
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $blocksData = $data['blocks_builder'] ?? [];
        unset($data['blocks_builder']);

        /** @var Page $record */
        $record->update($data);

        $this->syncBlocks($record, $blocksData);

        return $record;
    }

    protected function syncBlocks(Page $page, array $blocksData): void
    {
        $page->blocks()->delete();
        foreach ($blocksData as $idx => $entry) {
            $type = $entry['type'] ?? null;
            $payload = $entry['data'] ?? [];
            if (! $type) continue;

            $isActive = (bool) ($payload['__is_active'] ?? true);
            unset($payload['__is_active'], $payload['__block_id']);

            $page->blocks()->create([
                'type' => $type,
                'settings' => $payload,
                'sort_order' => $idx,
                'is_active' => $isActive,
            ]);
        }

        Page::clearCache($page->key);
    }
}
