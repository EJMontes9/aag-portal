<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?string $navigationLabel = 'Registro de actividad';
    protected static ?string $title           = 'Registro de actividad';
    protected static string  $view            = 'filament.pages.activity-log-page';
    protected static ?int    $navigationSort  = 10;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->width(150),

                Tables\Columns\TextColumn::make('event')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (?string $state) => match($state) {
                        'created'     => 'success',
                        'updated'     => 'info',
                        'deleted'     => 'danger',
                        'login'       => 'warning',
                        'logout'      => 'gray',
                        'mail_sent'   => 'primary',
                        'mail_failed' => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match($state) {
                        'created'     => '✚ Creado',
                        'updated'     => '✎ Actualizado',
                        'deleted'     => '✕ Eliminado',
                        'login'       => '→ Acceso',
                        'logout'      => '← Salida',
                        'mail_sent'   => '✉ Email enviado',
                        'mail_failed' => '⚠ Email fallido',
                        default       => $state ?? '—',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Objeto')
                    ->formatStateUsing(fn (?string $state) => $state
                        ? class_basename($state)
                        : '—')
                    ->description(fn (Activity $record) => $record->subject_id ? "ID #{$record->subject_id}" : null),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->default('Sistema / Visitante')
                    ->description(fn (Activity $record) => $record->causer?->email),

                Tables\Columns\TextColumn::make('properties')
                    ->label('Detalles')
                    ->formatStateUsing(function ($state, Activity $record) {
                        $props = $record->properties->toArray();
                        if (empty($props)) return '—';
                        // Show old/new for updated events
                        if (isset($props['old']) && isset($props['attributes'])) {
                            $changes = [];
                            foreach ($props['attributes'] as $key => $new) {
                                $old = $props['old'][$key] ?? null;
                                if ($old !== $new) {
                                    $changes[] = "{$key}: " . substr(json_encode($old), 0, 30) . ' → ' . substr(json_encode($new), 0, 30);
                                }
                            }
                            return implode("\n", array_slice($changes, 0, 4)) ?: '—';
                        }
                        // For other events, show key props
                        unset($props['user_agent']);
                        return collect($props)->map(fn($v, $k) => "$k: " . (is_array($v) ? json_encode($v) : substr((string)$v, 0, 50)))->implode("\n");
                    })
                    ->wrap()
                    ->limit(120),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Tipo de acción')
                    ->options([
                        'created'     => '✚ Creado',
                        'updated'     => '✎ Actualizado',
                        'deleted'     => '✕ Eliminado',
                        'login'       => '→ Acceso',
                        'logout'      => '← Salida',
                        'mail_sent'   => '✉ Email enviado',
                        'mail_failed' => '⚠ Email fallido',
                    ]),

                Tables\Filters\SelectFilter::make('causer_id')
                    ->label('Usuario')
                    ->options(
                        \App\Models\User::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->query(fn (Builder $query, array $data) =>
                        $data['value']
                            ? $query->where('causer_id', $data['value'])->where('causer_type', \App\Models\User::class)
                            : $query
                    ),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'],  fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = 'Desde: '  . $data['from'];
                        if ($data['until']) $indicators[] = 'Hasta: ' . $data['until'];
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_properties')
                    ->label('Ver detalles')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalContent(fn (Activity $record) => view('filament.activity-log-detail', ['activity' => $record]))
                    ->modalHeading('Detalles del evento')
                    ->modalWidth('lg')
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
