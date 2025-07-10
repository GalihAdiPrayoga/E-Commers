<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    public function index()
    {
        return OrderDetail::with(['order', 'product'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $orderDetail = OrderDetail::create($request->all());
        return response()->json($orderDetail);
    }

    public function show(OrderDetail $orderDetail)
    {
        return $orderDetail->load(['order', 'product']);
    }

    public function update(Request $request, OrderDetail $orderDetail)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
        ]);

        $orderDetail->update($request->all());
        return response()->json($orderDetail);
    }

    public function destroy(OrderDetail $orderDetail)
    {
        $orderDetail->delete();
        return response()->json(['message' => 'Order detail deleted']);
    }

    public function restore($id)
    {
        $orderDetail = OrderDetail::withTrashed()->findOrFail($id);
        $orderDetail->restore();
        return response()->json(['message' => 'Order detail restored successfully']);
    }
}
