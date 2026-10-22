<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = mb_substr(trim((string) $request->input('q', '')), 0, 100);
        $sort = in_array($request->input('sort'), ['price_asc', 'price_desc', 'name'], true)
            ? $request->input('sort') : 'latest';
        $query = Product::where('stock', '>', 0);
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };
        $products = $query->orderBy('id_product')->paginate(12)->withQueryString();
        return view('user.dashboard', compact('products', 'search', 'sort'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('user.products.show', compact('product'));
    }
}
