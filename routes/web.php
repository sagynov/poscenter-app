<?php

use App\Services\QrCodeService;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/qr', function(QrCodeService $qrService) {
  $url = 'https://kaspi.kz/pay/POSCENTERSOFTWARE?service_id=9975&14628=AR-00009999&amount=100000';
  $logoPath = public_path('images/kaspi-logo.png'); // Убедитесь, что файл существует
  
  try {
      $qrBase64 = $qrService->generateBase64WithLogo($url, $logoPath);
      
      // Если рендерите Blade-шаблон:
      return view('qr-show', compact('qrBase64'));
      
      // Если делаете API:
      // return response()->json(['qr_code' => $qrBase64]);
      
  } catch (\Exception $e) {
      abort(500, $e->getMessage());
  }
});

// require __DIR__.'/settings.php';
require __DIR__.'/bot.php';
