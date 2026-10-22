<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Product::query();
        $search = $request->input('search');
        if (is_string($search) && trim($search) !== '') {
            $query->where('name', 'like', '%' . mb_substr(trim($search), 0, 100) . '%');
        }
        match ($request->input('stock')) {
            'available' => $query->where('stock', '>', 0),
            'low' => $query->where('stock', '<=', 5),
            'out' => $query->where('stock', 0),
            default => null,
        };
        $products = $query->latest()->orderByDesc('id_product')->paginate(10)->withQueryString();
        $inventoryCounts = [
            'all' => Product::count(),
            'out' => Product::where('stock', 0)->count(),
            'low' => Product::whereBetween('stock', [1, 5])->count(),
        ];
        return view('admin.products.index', compact('products', 'inventoryCounts'));
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $productData = $validated;
        $productData['description'] = $validated['description'] ?? '';
        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('products', 'public');
            $productData['image'] = basename($filePath); // Simpan nama file saja
        }

        Product::create($productData);
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $productData = $validated;
        $productData['description'] = $validated['description'] ?? '';
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete('products/' . $product->image);
            }
            $filePath = $request->file('image')->store('products', 'public');
            $productData['image'] = basename($filePath); // Simpan nama file saja
        }

        $product->update($productData);
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete('products/' . $product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
