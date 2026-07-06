<?php

namespace App\Filament\Resources\FormSubmissionResource\Pages;

use App\Filament\Resources\FormSubmissionResource;
use App\Models\FormSubmission;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_read')
                ->label(fn () => $this->record->isRead() ? 'Marcar no leído' : 'Marcar leído')
                ->icon(fn () => $this->record->isRead() ? 'heroicon-o-envelope' : 'heroicon-o-envelope-open')
                ->color('gray')
                ->action(function () {
                    $this->record->isRead()
                        ? $this->record->markAsUnread()
                        : $this->record->markAsRead();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        // Marcar como leído al abrir
        $this->record->markAsRead();
    }
}
