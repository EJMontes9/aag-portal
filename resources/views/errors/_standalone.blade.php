{{--
    Pagina de error AUTONOMA para 500 y 503.

    Deliberadamente NO extiende layouts.app ni llama a settings(), Menu ni
    ningun modelo: estos dos errores se disparan justo cuando algo de eso
    puede estar caido (base de datos inaccesible, cache corrupta, sitio en
    mantenimiento). Si la plantilla de error dependiera de la BD, fallaria a su
    vez y Laravel acabaria mostrando su pantalla cruda por defecto, que es
    exactamente lo que se quiere evitar.

    Por el mismo motivo los colores van escritos literales en vez de leerse de
    los tokens: son los institucionales de la Propuesta B, y aqui no hay
    ningun sitio de donde leerlos. Las fuentes se sirven desde /fonts, que es
    Apache directamente, sin PHP de por medio.

    Parametros: $code, $titulo, $mensaje, $nota (opcional)
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }} · Autoridad Aeroportuaria de Guayaquil</title>
    <meta name="robots" content="noindex">
    <style>
        @font-face {
            font-family: 'Neulis Black';
            src: url('/fonts/neulis-black.otf') format('opentype');
            font-weight: 900; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Barlow Condensed';
            src: url('/fonts/BarlowCondensed-Regular.ttf') format('truetype');
            font-weight: 400; font-style: normal; font-display: swap;
        }
        @font-face {
            font-family: 'Barlow Condensed';
            src: url('/fonts/BarlowCondensed-Bold.ttf') format('truetype');
            font-weight: 700; font-style: normal; font-display: swap;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-size: 106.25%; }

        body {
            font-family: 'Barlow Condensed', 'Segoe UI', Arial, sans-serif;
            background: #F5F5F5;
            color: #222;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .caja {
            width: 100%;
            max-width: 640px;
            background: #fff;
            border: 1px solid #CCC;
            border-radius: 4px;
            overflow: hidden;
        }

        .cabecera {
            background: #2E2F63;
            border-bottom: 3px solid #EFC600;
            padding: 32px 32px 28px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .codigo {
            font-family: 'Neulis Black', Georgia, serif;
            font-size: 64px;
            line-height: 1;
            color: #EFC600;
            font-feature-settings: 'tnum';
        }

        .cabecera h1 {
            font-family: 'Neulis Black', Georgia, serif;
            font-size: 26px;
            line-height: 1.23;
            color: #fff;
            letter-spacing: 0.01em;
            font-weight: 400;
        }

        .cabecera p {
            margin-top: 10px;
            font-size: 15px;
            color: rgba(255, 255, 255, 0.8);
            max-width: 46ch;
        }

        .cuerpo { padding: 24px 32px; }

        .nota {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .acciones { display: flex; flex-wrap: wrap; gap: 12px; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 2px;
            padding: 12px 24px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color .15s, color .15s, border-color .15s;
        }
        .btn-primario { background: #2E2F63; color: #fff; }
        .btn-primario:hover { background: #009CDF; }
        .btn-secundario { background: #fff; color: #2E2F63; border-color: #CCC; }
        .btn-secundario:hover { border-color: #009CDF; color: #009CDF; }
        .btn:focus-visible { outline: 2px solid #009CDF; outline-offset: 2px; }

        .pie {
            border-top: 1px solid #CCC;
            background: #F5F5F5;
            padding: 16px 32px;
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 480px) {
            .cabecera { padding: 24px 20px; gap: 14px; }
            .codigo { font-size: 52px; }
            .cuerpo, .pie { padding-left: 20px; padding-right: 20px; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="caja">
        <div class="cabecera">
            <span class="codigo">{{ $code }}</span>
            <div>
                <h1>{{ $titulo }}</h1>
                <p>{{ $mensaje }}</p>
            </div>
        </div>

        <div class="cuerpo">
            @if(!empty($nota))
                <p class="nota">{{ $nota }}</p>
            @endif
            <div class="acciones">
                <a href="/" class="btn btn-primario">Ir al inicio</a>
                <button type="button" class="btn btn-secundario" onclick="location.reload()">Reintentar</button>
            </div>
        </div>

        <div class="pie">
            Autoridad Aeroportuaria de Guayaquil
        </div>
    </main>
</body>
</html>
