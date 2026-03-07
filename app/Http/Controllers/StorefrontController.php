<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function home(): View
    {
        return view('storefront.home');
    }

    public function products(): View
    {
        $products = Product::query()->where('is_active', true)->latest()->get();

        return view('storefront.products', compact('products'));
    }

    public function showProduct(Product $id): View
    {
        return view('storefront.product', ['product' => $id]);
    }
}
