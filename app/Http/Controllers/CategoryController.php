<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Categories/Index', [
            'filters' => \Illuminate\Support\Facades\Request::all('search', 'role', 'trashed'),
            'categories' => new CategoryCollection(Category::whereIn(
                'shop_id',
                Auth::user()->account->shops()->pluck('id')
            )->with('shop')->paginate()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        return Inertia::render('Categories/Create')->with([
            'shop' => $id
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $shop = Shop::findOrFail($validated['shop_id']);

        $category = $shop->categories()->create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        if ($request->hasFile('image')) {
            $category->update([
                'image' => $request->file('image')->store('public'),
            ]);
        }

        return redirect()->back()->with('success', 'Категория добавлен успешно');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = Category::with('products')->find($id);

        return Inertia::render('Categories/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $id)
    {
        $category = Category::findOrFail($id);

        $category->update(
            $request->validated()
        );

        if ($request->hasFile('image')) {
            $category->update([
                'image' => $request->file('image')->store('public/category'),
            ]);
        }

        return Redirect::back()->with('success', 'Категория обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
