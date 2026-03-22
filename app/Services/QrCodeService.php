<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Exception;

class QrCodeService
{
    /**
     * Генерирует QR-код с логотипом и возвращает его в формате Base64 (Data URI).
     *
     * @param string $data Ссылка или текст для QR-кода
     * @param string $logoPath Абсолютный путь к файлу логотипа
     * @return string Готовый data:image/png;base64,...
     * @throws Exception
     */
    public function generateBase64WithLogo(string $data, string $logoPath): string
    {
        if (!file_exists($logoPath)) {
            throw new Exception("Логотип не найден по пути: {$logoPath}");
        }

        // 1. Настраиваем параметры QR-кода (специфика версии 5)
        $options = new QROptions([
            'version'          => QRCode::VERSION_AUTO,         // Автоматический размер сетки
            'eccLevel'         => QRCode::ECC_H,                // Максимальная коррекция ошибок (ОБЯЗАТЕЛЬНО)
            'outputType'       => QROutputInterface::GDIMAGE_PNG, // Используем GD PNG
            'outputBase64'     => false,                        // Получаем сырые бинарные данные
            'scale'            => 10,                           // Размер пикселя
            'imageTransparent' => false,                        // Белый фон
        ]);

        // 2. Генерируем базовый QR-код
        $qrCode = new QRCode($options);
        $qrImageData = $qrCode->render($data);

        // 3. Создаем ресурсы изображений в памяти
        $qrImage = imagecreatefromstring($qrImageData);
        $logoImage = imagecreatefromstring(file_get_contents($logoPath));

        if (!$qrImage || !$logoImage) {
            throw new Exception("Ошибка при создании изображений из данных.");
        }

        // 4. Получаем размеры
        $qrWidth = imagesx($qrImage);
        $qrHeight = imagesy($qrImage);
        $logoWidth = imagesx($logoImage);
        $logoHeight = imagesy($logoImage);

        // 5. Вычисляем новый размер логотипа (25% от ширины QR-кода)
        $newLogoWidth = $qrWidth / 4; 
        $newLogoHeight = ($logoHeight / $logoWidth) * $newLogoWidth;

        // Координаты для центрирования
        $x = ($qrWidth - $newLogoWidth) / 2;
        $y = ($qrHeight - $newLogoHeight) / 2;

        // 6. Сохраняем прозрачность для логотипа (если это PNG)
        imagealphablending($qrImage, true);
        imagesavealpha($qrImage, true);

        // 7. Накладываем логотип на QR-код
        imagecopyresampled(
            $qrImage, $logoImage, 
            (int) $x, (int) $y, 0, 0, 
            (int) $newLogoWidth, (int) $newLogoHeight, 
            $logoWidth, $logoHeight
        );

        // 8. Перехватываем вывод картинки в буфер
        ob_start();
        imagepng($qrImage);
        $finalImageData = ob_get_clean();

        // 9. Очищаем память
        imagedestroy($qrImage);
        imagedestroy($logoImage);

        // 10. Формируем Base64 строку, готовую для использования в HTML
        return 'data:image/png;base64,' . base64_encode($finalImageData);
    }
}