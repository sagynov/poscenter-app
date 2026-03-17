<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\TelegramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function user(Request $request): TelegramUser
    {
        return $request->attributes->get('tg_user');
    }

    /**
     * GET /api/cart
     */
    public function index(Request $request): JsonResponse
    {
        $items = CartItem::with('product')
            ->where('telegram_user_id', $this->user($request)->id)
            ->get()
            ->map(fn ($item) => [
                'id'       => $item->id,
                'quantity' => $item->quantity,
                'product'  => [
                    'id'       => $item->product->id,
                    'name'     => $item->product->name,
                    'slug'     => $item->product->slug,
                    'price'    => (float) $item->product->price,
                    'image'    => $item->product->images[0] ?? null,
                    'in_stock' => $item->product->stock > 0,
                ],
            ]);

        $total = $items->sum(fn ($i) => $i['quantity'] * $i['product']['price']);

        return response()->json([
            'data'  => $items,
        ]);
    }

    /**
     * POST /api/cart
     * Body: { product_id: int, quantity: int }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'integer|min:1|max:99',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < 1) {
            return response()->json(['error' => 'Product out of stock'], 422);
        }

        $item = CartItem::updateOrCreate(
            [
                'telegram_user_id' => $this->user($request)->id,
                'product_id'       => $product->id,
            ],
            ['quantity' => $request->input('quantity', 1)]
        );

        return response()->json(['data' => $item], 201);
    }

    /**
     * PATCH /api/cart/{id}
     * Body: { quantity: int }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $item = CartItem::where('id', $id)
            ->where('telegram_user_id', $this->user($request)->id)
            ->firstOrFail();

        $item->update(['quantity' => $request->quantity]);

        return response()->json(['data' => $item]);
    }

    /**
     * DELETE /api/cart/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        CartItem::where('id', $id)
            ->where('telegram_user_id', $this->user($request)->id)
            ->firstOrFail()
            ->delete();

        return response()->json(null, 204);
    }

    /**
     * DELETE /api/cart
     * Clear entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        CartItem::where('telegram_user_id', $this->user($request)->id)->delete();

        return response()->json(null, 204);
    }
}