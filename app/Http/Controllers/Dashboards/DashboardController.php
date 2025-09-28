<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
		if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'superAdmin' && auth()->user()->role !== 'user') {
			return redirect()->route('orders.index');
		}
        if (auth()->user()->role === 'user') {
            $orders = Order::where("user_id", auth()->user()->id)
                ->whereDate('created_at', today())
                ->count();
        } else {
            $orders = Order::whereDate('created_at', today())
                ->count();
        }


        if (auth()->user()->role === 'user') {
            $thisorders = Order::where('user_id', auth()->user()->id)
                ->whereDate('created_at', today())
                ->get();
        } else {
            $thisorders = Order::whereDate('created_at', today())->get();
        }

        $totalSales = 0;
        foreach ($thisorders as $order) {
            if (!empty($order->org_total) && $order->org_total > 0) {
                $totalSales += $order->org_total;
            }

            if (!empty($order->total) && $order->total > 0) {
                $totalSales += $order->total;
            }
        }

        $products = Product::count();
        $productsQuantity = Product::sum('quantity');
        $purchases = Purchase::where("user_id", auth()->user()->id)->count();
        $todayPurchases = Purchase::whereDate('date', today()->format('Y-m-d'))->count();
        $todayProducts = Product::whereDate('created_at', today()->format('Y-m-d'))->count();
        $todayQuotations = Quotation::whereDate('created_at', today()->format('Y-m-d'))->count();
        $todayOrders = Order::whereDate('created_at', today()->format('Y-m-d'))->count();
        $categories = Category::where("user_id", auth()->user()->id)->count();
        $quotations = Quotation::where("user_id", auth()->user()->id)->count();
        return view('dashboard', [
            'products' => $products,
            'productsQuantity' => $productsQuantity,
            'orders' => $orders,
            'totalSales' => $totalSales,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'todayProducts' => $todayProducts,
            'todayQuotations' => $todayQuotations,
            'todayOrders' => $todayOrders,
            'categories' => $categories,
            'quotations' => $quotations
        ]);

    }
}
