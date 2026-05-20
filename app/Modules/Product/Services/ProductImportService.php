<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\DTOs\ProductSearchCriteria;

class ProductImportService
{
    public function __construct(
        protected ProductService $productService,
        protected VariantService $variantService,
    ) {}

    public function validateCSV(string $filePath): array
    {
        $errors = [];
        $rows = array_map('str_getcsv', file($filePath));

        if (empty($rows)) {
            return ['error' => 'Empty CSV file'];
        }

        $headers = array_shift($rows);

        foreach ($rows as $index => $row) {
            $line = $index + 2;

            if (count($row) !== count($headers)) {
                $errors[] = "Line {$line}: Column count mismatch";

                continue;
            }

            $data = array_combine($headers, $row);

            if (empty($data['name'])) {
                $errors[] = "Line {$line}: Name is required";
            }

            if (empty($data['base_price'])) {
                $errors[] = "Line {$line}: Base price is required";
            }
        }

        return $errors;
    }

    public function importCSV(string $filePath, array $options = []): array
    {
        $rows = array_map('str_getcsv', file($filePath));
        $headers = array_shift($rows);

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $data = array_combine($headers, $row);

                $dto = ProductDTO::fromRequest([
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?? '',
                    'sku' => $data['sku'] ?? null,
                    'type' => $data['type'] ?? 'simple',
                    'status' => $data['status'] ?? 'draft',
                    'base_price' => (int) ($data['base_price'] ?? 0),
                    'category_id' => isset($data['category_id']) ? (int) $data['category_id'] : null,
                    'brand_id' => isset($data['brand_id']) ? (int) $data['brand_id'] : null,
                    'description' => $data['description'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'track_inventory' => (bool) ($data['track_inventory'] ?? true),
                    'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?? 5),
                ]);

                $this->productService->createProduct($dto);
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function exportProducts(ProductSearchCriteria $criteria, string $format = 'csv'): string
    {
        $products = $this->productService->listProducts($criteria);

        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, [
            'id', 'name', 'slug', 'sku', 'type', 'status',
            'base_price', 'category_id', 'brand_id',
            'track_inventory', 'low_stock_threshold',
        ]);

        foreach ($products as $product) {
            fputcsv($csv, [
                $product->id,
                $product->name,
                $product->slug,
                $product->sku,
                $product->type?->value,
                $product->status?->value,
                $product->base_price,
                $product->category_id,
                $product->brand_id,
                $product->track_inventory,
                $product->low_stock_threshold,
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $content;
    }
}
