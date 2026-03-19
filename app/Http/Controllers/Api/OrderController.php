<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
          'name' => 'required|string',
          'phone' => 'required|string',
          'city' => 'required|string',
          'shipping_address' => 'required|string',
          'note' => 'nullable|string',
          'payment_method' => 'required|string'
        ]);
        $user = $request->attributes->get('tg_user');
        if ($user->cart_items()->exists()) {
          $cartItems = $user->cart_items()->with('product')->get();
          $validated['items'] = $cartItems->map(fn($item) => [
              'name'     => $item->product->name,
              'price'    => $item->product->price,
              'quantity' => $item->quantity,
              'subtotal' => $item->product->price * $item->quantity,
          ])->toArray();
          $validated['status'] = 'pending';
          $validated['total'] = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

          DB::transaction(function () use ($user, $validated) {
              $user->orders()->create($validated);
              $user->cart_items()->delete();
          });

          return response()->json([
              'success' => true,
              'message' => 'Успех'
          ]);
      } else {
          return response()->json([
              'success' => false,
              'message' => 'Ошибка. Корзина пуста'
          ]);
      }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
