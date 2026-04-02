<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XmlImportService
{
    public function import(string $url, ?int $category_id): array
    {
        $xml = $this->fetchXml($url);

        $xml->registerXPathNamespace('k', 'kaspiShopping');
        $offers = $xml->xpath('//k:offer');

        $imported = 0;
        $errors   = 0;

        foreach ($offers as $offer) {
            try {
                DB::transaction(fn () => $this->processOffer($offer, $category_id));
                $imported++;
            } catch (\Throwable $e) {
                Log::error('XML import error', [
                    'sku'   => (string) $offer['sku'],
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        return compact('imported', 'errors');
    }

    private function fetchXml(string $url): \SimpleXMLElement
    {
        $response = Http::timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("Не удалось загрузить XML: HTTP {$response->status()}");
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response->body());

        if (!$xml) {
            $errors = array_map(fn ($e) => $e->message, libxml_get_errors());
            libxml_clear_errors();
            throw new \RuntimeException('Ошибка парсинга XML: ' . implode(', ', $errors));
        }

        return $xml;
    }

    private function processOffer(\SimpleXMLElement $offer, ?int $category_id): void
    {
        $sku      = (string) $offer['sku'];
        $name     = (string) $offer->model;
        $price    = (float)  $offer->price;
        $oldPrice = isset($offer->oldprice) ? (float) $offer->oldprice : null;

        $product = Product::where('slug', $sku)->first();

        $data = [
            'name'       => $name,
            'price'      => $price,
            'old_price'  => $oldPrice,
        ];
        foreach ($offer->availabilities->availability as $avail) {
            $data['stock'] = (int) $avail['stockCount'];
        }
        if(!$product) {
          // Создаем если нет
          $data['slug'] = $sku;
          $data['category_id'] = $category_id ? $category_id : config('app.uncategorized_id');
          $data['sort_order'] = 100;
          $product = Product::create($data);
        }
        // обновим если есть
        $product->update($data);
        
    }
}