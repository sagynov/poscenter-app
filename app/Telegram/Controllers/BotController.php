<?php
namespace App\Telegram\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

use App\Telegram\System;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class BotController
{
    public function index(Request $request)
    {        
        $system = new System();
        $system->setRequest($request);
        $user = $system->user;
        if(isset($request->message)){
            $controller = new Bot\MessageController();
            return $controller->index($system);
        }
        elseif (isset($request->callback_query)) {
            $controller = new Bot\CallbackController();
            return $controller->index($system);
        }
        return 'success';
    }
    public function webapp(Request $request): Response
    {
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

        $user = $request->attributes->get('tg_user');

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

        return Inertia::render('Bot/WebApp', [
            'categories' => $categories,
            'products'   => $products,
            'cartItems'  => $cartItems
        ]);
    }
    public function cart(Request $request): Response
    {
        $user = $request->attributes->get('tg_user'); // если используешь tma.auth middleware

        $items = CartItem::with('product')
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
            ]);

        return Inertia::render('Bot/Cart', [
            'items' => $items,
        ]);
    }
}