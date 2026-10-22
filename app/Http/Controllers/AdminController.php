<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class AdminController extends Controller
{
    public function index()
    {
        $periodStart = now()->subDays(30);
        $previousStart = now()->subDays(60);
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $newProductsThisMonth = Product::where('created_at', '>=', now()->startOfMonth())->count();
        $activeUsers = User::where('is_admin', false)->where('created_at', '>=', $periodStart)->count();
        $recentOrders = Order::with(['user', 'products'])->latest()->take(6)->get();
        $pendingOrders = Order::where('payment_status', 'pending')->count();
        $ordersAwaitingReview = Order::where('payment_status', 'pending')->whereNotNull('payment_proof')->count();
        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock')->orderBy('name')->take(5)->get();
        $approvedOrders = Order::whereIn('payment_status', ['approved', 'completed'])->with('products')->get();
        $orderValue = fn ($order) => $order->products->sum(fn ($product) => $product->pivot->quantity * $product->pivot->price_at_order);
        $revenue = $approvedOrders->sum($orderValue);
        $recentRevenue = $approvedOrders->where('created_at', '>=', $periodStart)->sum($orderValue);
        $previousRevenue = $approvedOrders->where('created_at', '>=', $previousStart)->where('created_at', '<', $periodStart)->sum($orderValue);
        $revenueTrend = $this->trend($recentRevenue, $previousRevenue);
        $orderTrend = $this->trend(
            Order::where('created_at', '>=', $periodStart)->count(),
            Order::where('created_at', '>=', $previousStart)->where('created_at', '<', $periodStart)->count(),
        );
        $recentActivities = $this->activityFeed(5);

        return view('admin.dashboard', compact(
            'totalOrders', 'totalProducts', 'newProductsThisMonth', 'activeUsers',
            'recentOrders', 'pendingOrders', 'ordersAwaitingReview', 'lowStockProducts',
            'revenue', 'revenueTrend', 'orderTrend', 'recentActivities',
        ));
    }

    public function activities()
    {
        return view('admin.activities', ['activities' => $this->activityFeed(30)]);
    }

    private function trend(float $current, float $previous): float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : ($current > 0 ? 100 : 0);
    }

    private function activityFeed(int $limit): Collection
    {
        $orders = Order::with('user')->latest()->take($limit)->get()->map(fn ($order) => [
            'type' => 'bag', 'title' => 'Pesanan baru',
            'description' => 'Pesanan #' . $order->id_order . ' oleh ' . ($order->customer_name ?: $order->user?->name ?: 'Pelanggan'),
            'created_at' => $order->created_at,
        ]);
        $products = Product::latest()->take($limit)->get()->map(fn ($product) => [
            'type' => 'package', 'title' => 'Produk ditambahkan',
            'description' => $product->name . ' masuk ke katalog.', 'created_at' => $product->created_at,
        ]);
        $users = User::where('is_admin', false)->latest()->take($limit)->get()->map(fn ($user) => [
            'type' => 'user', 'title' => 'Pelanggan baru',
            'description' => $user->name . ' membuat akun pelanggan.', 'created_at' => $user->created_at,
        ]);

        return $orders->concat($products)->concat($users)->sortByDesc('created_at')->take($limit)->values();
    }
}
