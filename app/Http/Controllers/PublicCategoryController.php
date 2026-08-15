<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class PublicCategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->withCount(['tourismServices' => fn ($query) => $query->whereHas('serviceProvider', fn ($provider) => $provider->publiclyOperational())])->orderBy('category_name')->get();

        return view('public.categories.index', compact('categories'));
    }
}
