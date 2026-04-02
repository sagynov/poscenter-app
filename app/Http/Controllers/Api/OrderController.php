<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;
use App\Services\Wappi;
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
    public function store(Request $request, Wappi $wappi, QrCodeService $qrService)
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
        // сохраним данные клиента
        $user->update([
          'phone' => $validated['phone'],
          'city' => $validated['city'],
          'shipping_address' => $validated['shipping_address'],
        ]);

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
          
          $order = $user->orders()->create($validated);

          // Уменьшаем stock
          foreach ($cartItems as $item) {
                if($item->product->stock > 0 && $item->quantity <= $item->product->stock) {
                  $item->product->decrement('stock', $item->quantity);
                }else{
                  $item->product->update(['stock' => 0]);
                }
          }

          $user->cart_items()->delete();
      
          // Отправим уведомление
          $text = 'Новый заказ в боте t.me/poscenter_zakaz_termo_bot'."\n\n";
          $text .= 'Номер заказа: *№'.$order->id.'*'."\n";
          $text .= 'Имя клиента: *'.$order->name.'*'."\n";
          $text .= 'Телефон: *'.('+'.$order->phone).'*'."\n";
          $text .= 'Город: *'.$order->city.'*'."\n";
          $text .= 'Адрес доставки: *'.$order->shipping_address.'*'."\n";
          $text .= 'Сумма заказа: *'.$order->total.' KZT*'."\n\n";
          $text .= 'Состав заказа:'."\n";
          foreach($order->items as $item) {
            $text .= $item['name'].': '.$item['price'].' * '.$item['quantity'].' = '.$item['subtotal'].' KZT'."\n";
          }
          $text .= "\n";
          
          $text .= 'Свяжитесь с клиентом для деталей. Удачи!';
          $recipient = config('services.wappi.recipient');
          try {
            $wappi->sendMessage($recipient, $text);
          } catch (\Exception $e) {
            \Log::error('Wappi sendOrderNotification failed', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
          }
          

          $url = 'https://kaspi.kz/pay/POSCENTERSOFTWARE?service_id=9975&14628=AR-00009999&amount='.$validated['total'];
          $logoPath = public_path('images/kaspi-logo.png'); // Убедитесь, что файл существует
          $qrBase64 = $qrService->generateBase64WithLogo($url, $logoPath);

          return response()->json([
              'success' => true,
              'message' => 'Успех',
              'qrBase64' => $qrBase64,
              'url' => $url
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
