<?php

namespace App\Filament\Resources\ApiTokenResource\Pages;

use App\Filament\Resources\ApiTokenResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;

    public function getSubheading(): ?string
    {
        return config('api.enabled')
            ? 'La API está activada. Los tokens de esta lista dan acceso de lectura a /api/v1.'
            : 'La API está desactivada (API_ENABLED=false): /api/v1 responde 404 y ningún token funciona. Puedes preparar tokens ahora y activarla después.';
    }

    protected function getHeaderActions(): array
    {
        // Solo va aqui la accion que se pinta como boton. `mostrarTokenAction`
        // se resuelve por su nombre de metodo cuando `crear` la invoca.
        return [
            $this->accionCrear(),
        ];
    }

    /**
     * Emite un token nuevo.
     *
     * El token se emite a nombre de quien lo crea. Podria pedirse el usuario en
     * el formulario, pero eso permitiria emitir credenciales en nombre de otra
     * persona y el registro de quien lo hizo dejaria de valer para nada.
     */
    private function accionCrear(): Actions\Action
    {
        return Actions\Action::make('crear')
            ->label('Crear token')
            ->icon('heroicon-m-plus')
            ->modalHeading('Crear token de API')
            ->modalSubmitActionLabel('Crear')
            ->form([
                TextInput::make('name')
                    ->label('Nombre')
                    ->helperText('Para qué es. Un nombre concreto ("App móvil", "Web del municipio") permite revocar el acceso correcto meses después.')
                    ->required()
                    ->maxLength(100),
            ])
            ->action(function (array $data): void {
                $token = auth()->user()->createToken($data['name']);

                // El valor en claro solo existe en este instante: la tabla
                // guarda un hash. Se pasa al modal siguiente en vez de a una
                // notificacion porque hay que poder leerlo con calma y
                // copiarlo, y una notificacion se desvanece.
                //
                // replaceMountedAction cierra este modal y abre el otro en la
                // misma interaccion, sin recargar la pagina.
                $this->replaceMountedAction('mostrarToken', [
                    'token' => $token->plainTextToken,
                    'nombre' => $data['name'],
                ]);
            });
    }

    /**
     * Muestra el token recien creado. Es la UNICA vez que se puede ver.
     *
     * Se declara como metodo publico "<nombre>Action" y NO se registra entre
     * las acciones de cabecera: Filament resuelve por reflexion las acciones
     * que no estan cacheadas, asi que replaceMountedAction la encuentra sin que
     * se pinte un boton para ella.
     *
     * El camino aparentemente obvio —registrarla como accion de cabecera y
     * marcarla ->hidden()— NO funciona: Action::isDisabled() considera
     * deshabilitada toda accion oculta, y mountAction() descarta en silencio
     * las deshabilitadas. El modal no llegaba a abrirse y el token se perdia
     * sin que nadie lo viera.
     */
    public function mostrarTokenAction(): Actions\Action
    {
        return Actions\Action::make('mostrarToken')
            ->modalHeading('Token creado')
            ->modalIcon('heroicon-o-key')
            ->modalIconColor('warning')
            ->modalDescription(new HtmlString(
                'Copia este token <strong>ahora</strong>. Solo se guarda cifrado, así que no se podrá volver a ver: '
                . 'si lo pierdes, tendrás que revocarlo y crear otro. Trátalo como una contraseña.'
            ))
            ->modalContent(fn (array $arguments) => view('filament.resources.api-token-resource.token-creado', [
                'token' => $arguments['token'] ?? '',
                'nombre' => $arguments['nombre'] ?? '',
            ]))
            // Sin boton de enviar: aqui no hay nada que confirmar, solo copiar.
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Ya lo he copiado');
    }
}
