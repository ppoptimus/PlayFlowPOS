<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $branchId = $this->reportService->resolveBranchId(
            $request->input('branch_id') ? (int) $request->input('branch_id') : null
        );

        $tab = $request->input('tab', 'sales');
        $period = $request->input('period', 'daily');
        $today = now()->toDateString();
        $dateFrom = $request->input('date_from', $today);
        $dateTo = $request->input('date_to', $today);

        $salesReport = null;
        $serviceReport = null;
        $masseuseReport = null;
        $productReport = null;

        switch ($tab) {
            case 'services':
                $serviceReport = $this->reportService->getServiceReport($branchId, $dateFrom, $dateTo);
                break;
            case 'masseuse':
                $masseuseReport = $this->reportService->getMasseuseReport($branchId, $dateFrom, $dateTo);
                break;
            case 'products':
                $productReport = $this->reportService->getProductReport($branchId, $dateFrom, $dateTo);
                break;
            default:
                $salesReport = $this->reportService->getSalesReport($branchId, $period, $dateFrom, $dateTo);
                break;
        }

        return view('reports.index', [
            'activeTab' => $tab,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'branchId' => $branchId,
            'salesReport' => $salesReport,
            'serviceReport' => $serviceReport,
            'masseuseReport' => $masseuseReport,
            'productReport' => $productReport,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $branchId = $this->reportService->resolveBranchId(
            $request->input('branch_id') ? (int) $request->input('branch_id') : null
        );

        $tab = $request->input('tab', 'sales');
        $period = $request->input('period', 'daily');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $csvData = match ($tab) {
            'services' => $this->reportService->exportServiceCsv($branchId, $dateFrom, $dateTo),
            'masseuse' => $this->reportService->exportMasseuseCsv($branchId, $dateFrom, $dateTo),
            'products' => $this->reportService->exportProductCsv($branchId, $dateFrom, $dateTo),
            default => $this->reportService->exportSalesCsv($branchId, $period, $dateFrom, $dateTo),
        };

        $filename = $csvData['filename'];

        return new StreamedResponse(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $csvData['header']);
            foreach ($csvData['rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
