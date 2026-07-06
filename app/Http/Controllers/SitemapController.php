<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\News;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Genera el sitemap.xml dinámico.
     * Cacheado 1 hora para no golpear la BD en cada crawl de Google.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap_xml', now()->addHour(), function () {
            $pages    = Page::where('status', 'published')->get(['id', 'key', 'slug', 'updated_at']);
            $news     = News::published()->latest('published_at')->get(['slug', 'published_at', 'updated_at', 'cover_image']);
            $projects = Project::published()->orderBy('sort_order')->get(['slug', 'updated_at']);
            $faqs     = Faq::active()->count();   // Solo contamos — la página FAQ es única

            return view('seo.sitemap', compact('pages', 'news', 'projects', 'faqs'))->render();
        });

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Genera robots.txt dinámico para incluir la URL correcta del sitemap.
     */
    public function robots(): Response
    {
        return response(view('seo.robots')->render(), 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
