<?php

use App\Http\Controllers\ConvocatoriaController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriberController;
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

// Noticias publicas (van antes del catch-all)
Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/noticias/{slug}', [NewsController::class, 'show'])->name('news.show');

// FAQ
Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

// Proyectos y obras
Route::get('/proyectos', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/proyectos/{slug}', [ProjectController::class, 'show'])->name('projects.show');

// Convocatorias (van antes del catch-all)
Route::get('/convocatorias', [ConvocatoriaController::class, 'index'])->name('convocatorias.index');
Route::get('/convocatorias/{slug}', [ConvocatoriaController::class, 'show'])->name('convocatorias.show');

// Suscripcion al boletin
Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe.store');

Route::get('/{slug}', function (string $slug) {
    $page = Page::where('slug', $slug)->where('status', 'published')->with('activeBlocks')->first();
    abort_unless($page, 404);
    return view('page', compact('page'));
})->where('slug', '^(?!admin|api|livewire|storage|noticias|faq|proyectos|convocatorias|subscribe)[a-z0-9\-]+$')
  ->name('page.show');
