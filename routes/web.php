<?php

use App\Http\Controllers\ConvocatoriaController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TransparencyController;
use App\Livewire\VisualEditor;
use App\Models\Page;
use Illuminate\Support\Facades\Route;

// ── SEO ──────────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt',  [SitemapController::class, 'robots'])->name('robots');

// ── VISUAL EDITOR ─────────────────────────────────────────────────────────────
Route::get('/admin/visual-editor/{page}', VisualEditor::class)
    ->middleware(['web', 'auth'])
    ->name('visual-editor');

Route::get('/', function () {
    $page = Page::byKey('home');
    abort_unless($page, 404);
    return view('page', compact('page'));
})->name('home');

// ── Paginas con BUSQUEDA ────────────────────────────────────────────────────
// Llevan limite de peticiones porque su busqueda hace LIKE '%texto%' sobre
// columnas largas y sin indice util: cada consulta recorre la tabla entera.
// Sin limite, un script puede saturar la base de datos desde una sola IP.
// 60 por minuto es holgado para una persona navegando.
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
    Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/convocatorias', [ConvocatoriaController::class, 'index'])->name('convocatorias.index');

    // El buscador global es el caso extremo de lo anterior: una sola peticion
    // lanza cinco LIKE '%texto%' sobre cinco tablas distintas.
    Route::get('/buscar', [SearchController::class, 'index'])->name('search');

    // Detalle de un mes de Transparencia, pedido bajo demanda por el bloque
    // "Navegador de Transparencia" (ver transparency-browser.blade.php). Va en
    // este mismo grupo con limite de peticiones porque, igual que el buscador,
    // hace una consulta a la base de datos en cada llamada.
    Route::get('/transparencia/mes/{month}/documentos', [TransparencyController::class, 'documentos'])
        ->whereNumber('month')
        ->name('transparency.month.documents');
});

// Fichas de detalle: consultas por clave indexada, sin busqueda.
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::get('/proyectos/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/convocatorias/{slug}', [ConvocatoriaController::class, 'show'])->name('convocatorias.show');

// Suscripcion al boletin: envia correo y escribe en base de datos, asi que el
// limite es mucho mas estrecho. El controlador ademas aplica su propio control
// por direccion de correo.
Route::post('/subscribe', [SubscriberController::class, 'store'])
    ->middleware('throttle:10,10')
    ->name('subscribe.store');

Route::get('/{slug}', function (string $slug) {
    $page = Page::where('slug', $slug)->where('status', 'published')->with('activeBlocks')->first();
    abort_unless($page, 404);
    return view('page', compact('page'));
})->where('slug', '^(?!admin|api|livewire|storage|noticias|faq|proyectos|convocatorias|buscar|subscribe)[a-z0-9\-]+$')
  ->name('page.show');
