<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::with('category')->get();
    }

    public function store(Request $request)
{

    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required',
        'description' => 'nullable',
        'price' => 'required|numeric',
        'quantity' => 'required|integer',
        'image' => 'nullable|array',
        'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->except('image');
    $imagePaths = [];   

    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $img) {
            $path = $img->store('products', 'public');
            $imagePaths[] = basename($path);
        }
    }

    $data['image'] = $imagePaths;

    $product = Product::create($data);

    $productArray = $product->toArray();
    $productArray['image'] = collect($imagePaths)->map(fn($img) => url('storage/products/' . $img));

    return response()->json([
        'message' => 'Product created successfully',
        'data' => $productArray
    ]);
}

    public function show(Product $product)
    {
        $product->load('category');
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'description' => $product->description,
            'category' => $product->category,
        ]);
    }

   public function update(Request $request, Product $product)
{

    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required',
        'description' => 'nullable',
        'price' => 'required|numeric',
        'quantity' => 'required|integer',
        'image' => 'nullable|array',
        'image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->except('image');
    $imagePaths = $product->image ?? [];

    // Jika ada file gambar baru, hapus gambar lama
    if ($request->hasFile('image')) {
        // Hapus gambar lama dari storage
        if (!empty($imagePaths)) {
            foreach ($imagePaths as $oldImg) {
                $oldPath = storage_path('app/public/products/' . $oldImg);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
        }
        $imagePaths = [];
        foreach ($request->file('image') as $img) {
            $path = $img->store('products', 'public');
            $imagePaths[] = basename($path);
        }
    }

    $data['image'] = $imagePaths;

    $product->update($data);

    $productArray = $product->toArray();
    $productArray['image'] = collect((array) $product->image)->map(fn($img) => url('storage/products/' . $img));

    return response()->json([
        'message' => 'Product updated successfully',
        'data' => $productArray
    ]);
}


    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();
        return response()->json(['message' => 'Product restored successfully']);
    }
}
