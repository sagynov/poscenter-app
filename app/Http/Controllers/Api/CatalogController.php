<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\TelegramUser;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->attributes->get('tg_user');
        $categories = Category::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'image']);

        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(20)
            ->get()
            ->map(fn ($p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'slug'     => $p->slug,
                'price'    => (float) $p->price,
                'old_price'=> $p->old_price ? (float) $p->old_price : null,
                'image'    => $p->images[0] ?? null,
                'in_stock' => $p->stock > 0,
                'category' => $p->category->slug,
            ]);

        $cartItems = $user
            ? CartItem::with('product')
                ->where('telegram_user_id', $user->id)
                ->get()
                ->map(fn ($item) => [
                    'id'       => $item->id,
                    'quantity' => $item->quantity,
                    'product'  => [
                        'id'    => $item->product->id,
                        'name'  => $item->product->name,
                        'slug'  => $item->product->slug,
                        'price' => (float) $item->product->price,
                        'image' => $item->product->images[0] ?? null,
                    ],
                ])
            : [];

        return response()->json([
          'categories' => $categories,
          'products' => $products,
          'cart' => $cartItems
        ]);
    }
}
