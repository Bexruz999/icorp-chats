<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductResource;
use App\Http\Requests\UpdateProductResource;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Products/Index', [
            'filters' => \Illuminate\Support\Facades\Request::all('search', 'role', 'trashed'),
            'products' => new ProductCollection(
                Product::with('category', 'shop')->whereIn('shop_id', Auth::user()->account->shops()->pluck('id'))->paginate()
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createCategory($id)
    {
        return Inertia::render('Products/CreateCategory', [
            'category' => $id
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $shops = Auth::user()->account->shops()->get();

        $categories = Category::whereIn('shop_id', $shops->pluck('id'))->get();

        $options = [];
        foreach ($categories as $category) {
            $options[] = [
                'label' => "$category->name: (".$category->shop->name.')',
                'value' => $category->id
            ];
        }

        return Inertia::render('Products/Create', [
            'categories' => $options
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductResource $request)
    {
        $validated = $request->validated();

        $category = Category::findOrFail($validated['category']);

        Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'short_description' => $validated['short_description'],
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'],
            'category_id' => $validated['category'],
            'shop_id' => $category->shop_id,
            'image' => $request->has('image') ? $request->file('image')->store('products') : '',
            'slug' => Str::slug($validated['name'])
        ]);

        return redirect()->route('products.index')->with('success', 'Товар добавлен успешно');
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
        $product = Product::find($id);

        $categories = Category::whereIn('shop_id',
            Auth::user()->account->shops()->pluck('id'))->get();

        $options = [];
        foreach ($categories as $category) {
            $options[] = [
                'label' => "$category->name: (".$category->shop->name.')',
                'value' => $category->id
            ];
        }

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $options
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductResource $request, string $id)
    {
        $product = Product::find($id);

        $validated = $request->validated();

        $product->update(array_merge(
            $validated,
            [
                'slug' => Str::slug($validated['name']),
                'shop_id' => Category::findOrFail($validated['category_id'])->shop_id
            ]
        ));

        if ($request->has('image') && !is_null($request->file('image'))) {
            $product->update(['image' => $request->file('image')->store('products')]);
        }

        return redirect()->route('products.index')->with('success', 'Товар изменен успешно');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
