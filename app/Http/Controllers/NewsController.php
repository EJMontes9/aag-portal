<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $categories = NewsCategory::orderBy('sort_order')->orderBy('name')->get();

        $query = News::query()
            ->published()
            ->with('category', 'author')
            ->latest('published_at');

        if ($slug = $request->get('categoria')) {
            $cat = $categories->firstWhere('slug', $slug);
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        // La búsqueda se acota a 80 caracteres: más allá no aporta nada y evita
        // que se manden cadenas enormes que encarecen el LIKE innecesariamente.
        // (El valor va como binding de PDO, así que no hay inyección posible;
        //  esto es por coste, no por seguridad de la consulta.)
        $q = trim((string) $request->get('q'));
        $q = $q !== '' ? mb_substr($q, 0, 80) : '';

        if ($q !== '') {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                  ->orWhere('excerpt', 'like', "%{$q}%");
            });
        }

        // El link en el <head> anuncia el feed con ?format=rss (ver layouts/app.blade.php).
        // Si no se atiende aquí, ese enlace queda roto y los lectores de feeds reciben HTML.
        if ($request->get('format') === 'rss') {
            $feedItems = News::query()
                ->published()
                ->latest('published_at')
                ->limit(20)
                ->get();

            return response()
                ->view('seo.rss', ['news' => $feedItems])
                ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
        }

        $news = $query->paginate(9)->withQueryString();

        return view('pages.news.index', [
            'news' => $news,
            'categories' => $categories,
            'activeCategory' => $request->get('categoria'),
            'q' => $q,
        ]);
    }

    public function show(string $slug)
    {
        $item = News::where('slug', $slug)->published()->with('category', 'author')->firstOrFail();
        $item->incrementViews();

        $related = News::published()
            ->with('category', 'author')
            ->where('id', '!=', $item->id)
            ->when($item->category_id, fn ($q) => $q->where('category_id', $item->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.news.show', [
            'item' => $item,
            'related' => $related,
        ]);
    }
}
