<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Services\DashboardDataService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardExportController
{
    public function __construct(
        protected DashboardDataService $dashboardDataService,
    ) {}

    public function csv(Request $request): StreamedResponse
    {
        $days = (int) ($request->input('days', 30));
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->input('warehouse_id') : null;

        $data = $this->dashboardDataService->getDashboardData($days, $warehouseId);

        $response = new StreamedResponse(function () use ($data) {
            $output = fopen('php://output', 'wb');

            fputcsv($output, ['Section', 'Date', 'Label', 'Value', 'Value2']);

            fputcsv($output, ['Health Score', '', 'Score', $data['health_score']['score']]);
            fputcsv($output, ['Health Score', '', 'Grade', $data['health_score']['grade']]);
            fputcsv($output, ['Health Score', '', 'Low Stock Ratio', $data['health_score']['low_stock_ratio']]);
            fputcsv($output, ['Health Score', '', 'Dead Stock Ratio', $data['health_score']['dead_stock_ratio']]);
            fputcsv($output, ['Health Score', '', 'Avg Velocity', $data['health_score']['avg_velocity']]);

            fputcsv($output, []);
            fputcsv($output, ['Movement Trend', 'Date', 'Quantity In', 'Quantity Out', 'Net Movement']);
            foreach ($data['movement_trend'] as $row) {
                fputcsv($output, ['Movement Trend', $row['date'], $row['quantity_in'], $row['quantity_out'], $row['net_movement']]);
            }

            fputcsv($output, []);
            fputcsv($output, ['Stock Value Trend', 'Date', 'Value', '', '']);
            foreach ($data['stock_value_trend'] as $row) {
                fputcsv($output, ['Stock Value Trend', $row['date'], $row['value'], '', '']);
            }

            fputcsv($output, []);
            fputcsv($output, ['Classification', 'Class', 'Count', '', '']);
            foreach ($data['classification_distribution']['abc'] as $class => $count) {
                fputcsv($output, ['ABC Classification', strtoupper($class), $count, '', '']);
            }
            foreach ($data['classification_distribution']['velocity'] as $class => $count) {
                fputcsv($output, ['Velocity Classification', $class, $count, '', '']);
            }

            fputcsv($output, []);
            fputcsv($output, ['Dead Stock Trend', 'Date', 'Count', 'Value', '']);
            foreach ($data['dead_stock_trend'] as $row) {
                fputcsv($output, ['Dead Stock Trend', $row['date'], $row['dead_stock_count'], $row['dead_stock_value'], '']);
            }

            fputcsv($output, []);
            fputcsv($output, ['Top Moving Products', 'Product ID', 'Total Out', 'Movement Days', '']);
            foreach ($data['top_moving_products'] as $row) {
                fputcsv($output, ['Top Moving Products', $row['product_id'], $row['total_out'], $row['movement_days'], '']);
            }

            fputcsv($output, []);
            fputcsv($output, ['Forecast Accuracy', 'Model', 'Avg Accuracy (%)', 'Count', '']);
            foreach ($data['forecast_accuracy'] as $row) {
                fputcsv($output, ['Forecast Accuracy', $row['model_used'], $row['avg_accuracy'], $row['count'], '']);
            }

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="inventory-dashboard.csv"');

        return $response;
    }
}
