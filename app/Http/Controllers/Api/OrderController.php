<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Admin: Update status order
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $order->status = $request->status;
        $order->save();
        return response()->json([
            'message' => 'Status order berhasil diupdate',
            'order' => $order
        ]);
    }

    /**
     * User: Lihat riwayat pesanan sendiri
     */
    public function history(Request $request)
    {
        $orders = Order::with('orderDetails.product')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json($orders);
    }
    /**
     * Menampilkan daftar pesanan milik user yang sedang login
     */
    public function myOrders(Request $request)
    {
        $orders = Order::with('orderDetails.product')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();
        return response()->json($orders);
    }

    /**
     * Admin: Lihat semua order beserta user dan detailnya
     */
    public function index()
    {
        return Order::with(['user', 'orderDetails.product'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($request) {
            $items = $request->input('items');
            $total = 0;
            $orderDetails = [];

            // Cek stok dan hitung subtotal
            foreach ($items as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception('Produk tidak ditemukan');
                }
                if ($product->quantity < $item['quantity']) {
                    throw new \Exception('Stok produk ' . $product->name . ' tidak cukup');
                }
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                $orderDetails[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Simpan order
            $order = \App\Models\Order::create([
                'user_id' => $request->user()->id,
                'total_price' => $total,
                'status' => 'pending',
            ]);

            // Simpan order details dan update stok
            foreach ($orderDetails as $detail) {
                \App\Models\OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'subtotal' => $detail['subtotal'],
                ]);
                // Update stok produk
                $product = \App\Models\Product::find($detail['product_id']);
                $product->decrement('quantity', $detail['quantity']);
            }

            return $order->load('orderDetails.product');
        });

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    public function show(Order $order)
    {
        return $order->load(['user', 'orderDetails.product']);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_price' => 'required|numeric',
            'status' => 'required|in:pending,diproses,selesai,dibatalkan',
        ]);

        $order->update($request->only(['user_id', 'total_price', 'status']));
        return response()->json($order->load(['user', 'orderDetails.product']));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    public function restore($id)
    {
        $order = Order::withTrashed()->findOrFail($id);
        $order->restore();
        return response()->json(['message' => 'Order restored successfully']);
    }
}
