<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $categories = FaqCategory::orderBy('sort_order')->get();

        $query = Faq::active()->with('category')->orderBy('sort_order');

        if ($slug = $request->get('categoria')) {
            $cat = $categories->firstWhere('slug', $slug);
            if ($cat) {
                $query->where('category_id', $cat->id);
            }
        }

        if ($q = $request->get('q')) {
            $query->where(function ($x) use ($q) {
                $x->where('question', 'like', "%$q%")
                  ->orWhere('answer', 'like', "%$q%");
            });
        }

        // Agrupamos por categoría para mostrar como acordeón agrupado
        $faqs = $query->get();
        $grouped = $faqs->groupBy(fn ($f) => $f->category_id ?? 0);

        return view('pages.faq.index', [
            'categories' => $categories,
            'grouped' => $grouped,
            'faqs' => $faqs,
            'activeCategory' => $request->get('categoria'),
            'q' => $q,
        ]);
    }
}
