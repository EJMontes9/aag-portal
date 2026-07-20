{{--
    Contenido del modal que muestra un token recien creado.

    Es la unica vez que este valor es visible: la tabla guarda un hash, asi que
    ni el panel ni nadie puede recuperarlo despues. De ahi el aviso y el boton
    de copiar — obligar a seleccionar a mano una cadena de 48 caracteres es la
    forma mas segura de que alguien se la copie a medias.
--}}
<div class="space-y-4">
    @if ($nombre)
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Token <strong class="text-gray-950 dark:text-white">{{ $nombre }}</strong>
        </p>
    @endif

    <div
        x-data="{ copiado: false }"
        class="rounded-lg border border-warning-300 bg-warning-50 p-4 dark:border-warning-600 dark:bg-warning-400/10"
    >
        <code
            x-ref="token"
            class="block break-all font-mono text-sm text-gray-950 dark:text-white"
        >{{ $token }}</code>

        <x-filament::button
            size="sm"
            color="gray"
            icon="heroicon-m-clipboard-document"
            class="mt-3"
            x-on:click="
                navigator.clipboard.writeText($refs.token.textContent.trim());
                copiado = true;
                setTimeout(() => copiado = false, 2000);
            "
        >
            <span x-show="! copiado">Copiar token</span>
            <span x-show="copiado" x-cloak>Copiado</span>
        </x-filament::button>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        Se envía en la cabecera <code>Authorization: Bearer &lt;token&gt;</code>.
        Consulta <code>docs/API.md</code> para los endpoints disponibles.
    </p>
</div>
