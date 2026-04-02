<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\XmlImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(
        private readonly XmlImportService $importService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
          'url' => ['required', 'url'],
          'category_id' => ['nullable', 'integer'],
        ]);

        try {
            $result = $this->importService->import($request->input('url'), $request->input('category_id'));

            return response()->json([
                'success'  => true,
                'imported' => $result['imported'],
                'errors'   => $result['errors'],
                'message'  => "Импортировано: {$result['imported']}, ошибок: {$result['errors']}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}