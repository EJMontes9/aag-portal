<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Page;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\MaxWidth|string|null
    {
        return \Filament\Support\Enums\MaxWidth::Full;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $blocksData = $data['blocks_builder'] ?? [];
        unset($data['blocks_builder']);

        if (empty($data['key'])) {
            $data['key'] = Str::slug($data['slug'] ?? $data['title']);
        }

        /** @var Page $page */
        $page = Page::create($data);

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
        return $page;
    }
}
