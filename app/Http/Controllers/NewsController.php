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

        if ($q = $request->get('q')) {
            $query->where(function ($x) use ($q) {
                $x->where('title', 'like', "%$q%")
                  ->orWhere('excerpt', 'like', "%$q%");
            });
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
