<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductFavorite;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductFavoriteController extends ApiController
{
    public function index() {
        $firebaseUser = request()->user();

        $products = ProductFavorite::with(['product', 'product.productSkus'])
            ->where('firebase_user_id', $firebaseUser->id)->get();

        $data = [];
        foreach ($products as $key => $product) {
            $data[] = $product->product;
        }

        return $this->response($data);
    }

    public function store(Request $request) {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $product = ProductFavorite::create([
            'product_id' => $request->get('product_id'),
            'firebase_user_id' => request()->user()->firebase_user_id
        ]);

        return $this->response($product);
    }

    public function delete(Request $request) {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $result = ProductFavorite::where([
            'product_id' => $request->get('product_id'),
            'firebase_user_id' => request()->user()->firebase_user_id
        ])->delete();

        return $this->response($result);
    }
}
