<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        return view('user.cart.index', compact('cart'));
    }

    public function add(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image,
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produk ditambahkan ke keranjang!');
    }

    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Produk dihapus dari keranjang!');
    }

    public function update(Request $request, $productId)
    {
        $cart = session()->get('cart', []);
        if (!isset($cart[$productId])) {
            return redirect()->route('cart.index')->with('error', 'Produk tidak ada di keranjang.');
        }

        $action = $request->input('action');
        if ($action === 'decrease') {
            if ($cart[$productId]['quantity'] > 1) {
                $cart[$productId]['quantity']--;
            } else {
                unset($cart[$productId]);
            }
        } elseif ($action === 'increase') {
            $product = Product::find($productId);
            if ($product && $cart[$productId]['quantity'] < $product->stock) {
                $cart[$productId]['quantity']++;
            } else {
                return redirect()->route('cart.index')->with('error', 'Stok tidak mencukupi.');
            }
        } else {
            return redirect()->route('cart.index')->with('error', 'Aksi tidak valid.');
        }

        session()->put('cart', $cart);
        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui.');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong, tidak ada item untuk checkout.');
        }
        return view('user.checkout', compact('cart'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|string|in:bank_transfer,credit_card,e_wallet,cod',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            Log::warning('Cart is empty');
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong, tidak dapat membuat pesanan.');
        }

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || $product->stock < $item['quantity']) {
                Log::error('Stock insufficient for product', ['productId' => $productId, 'stock' => $product?->stock]);
                return redirect()->route('cart.index')->with('error', "Stok tidak mencukupi untuk produk {$item['name']}.");
            }
        }

        // Menghitung total harga dari item di keranjang
        $totalPrice = array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);

        $order = Order::create([
            'id_user' => Auth::id(), 
            'total_price' => $totalPrice,
            'status' => 'pending',
            'customer_name' => Auth::user()->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'order_date' => now(),
        ]);

        foreach ($cart as $productId => $item) {
            $order->products()->attach($productId, [
                'quantity' => $item['quantity'],
                'price_at_order' => $item['price'],
            ]);
            $product = Product::find($productId);
            $product->stock -= $item['quantity'];
            $product->save();
        }

        session()->forget('cart');
        return redirect()->route('user.dashboard')->with('success', 'Pesanan berhasil dibuat!');
    }

    public function orderHistory()
    {
        $orders = Order::where('id_user', Auth::id())->with('products')->latest()->get();
        return view('user.orders.history', compact('orders'));
    }
}