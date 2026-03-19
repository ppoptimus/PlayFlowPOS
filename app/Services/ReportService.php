<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ─── Sales Report (Module 13) ─────────────────────────────────────

    public function getSalesReport(int $branchId, string $period = 'daily', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $range = $this->resolveRange($period, $dateFrom, $dateTo);

        $salesData = $this->buildSalesData($branchId, $range['from'], $range['to'], $period);
        $summary = $this->buildSalesSummary($branchId, $range['from'], $range['to']);

        return [
            'period' => $period,
            'date_from' => $range['from']->toDateString(),
            'date_to' => $range['to']->toDateString(),
            'summary' => $summary,
            'data' => $salesData,
        ];
    }

    private function buildSalesSummary(int $branchId, Carbon $from, Carbon $to): array
    {
        $query = $this->baseOrderQuery($branchId)
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $totalSales = (float) (clone $query)->sum('o.grand_total');
        $totalOrders = (int) (clone $query)->count('o.id');
        $totalDiscount = (float) (clone $query)->sum('o.discount_amount');

        $paymentMethods = (clone $query)
            ->selectRaw("COALESCE(o.payment_method, 'cash') as method, SUM(o.grand_total) as total, COUNT(*) as cnt")
            ->groupBy(DB::raw("COALESCE(o.payment_method, 'cash')"))
            ->get()
            ->map(fn($r) => [
                'method' => (string) $r->method,
                'total' => (int) round((float) $r->total),
                'count' => (int) $r->cnt,
            ])->all();

        return [
            'total_sales' => (int) round($totalSales),
            'total_orders' => $totalOrders,
            'total_discount' => (int) round($totalDiscount),
            'avg_per_order' => $totalOrders > 0 ? (int) round($totalSales / $totalOrders) : 0,
            'payment_methods' => $paymentMethods,
        ];
    }

    private function buildSalesData(int $branchId, Carbon $from, Carbon $to, string $period): array
    {
        $groupFormat = $period === 'yearly' ? '%Y' : ($period === 'monthly' ? '%Y-%m' : '%Y-%m-%d');

        return $this->baseOrderQuery($branchId)
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to)
            ->selectRaw("DATE_FORMAT(o.created_at, '{$groupFormat}') as period_key, SUM(o.grand_total) as total_sales, COUNT(o.id) as total_orders, SUM(o.discount_amount) as total_discount")
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->map(fn($r) => [
                'period' => (string) $r->period_key,
                'total_sales' => (int) round((float) $r->total_sales),
                'total_orders' => (int) $r->total_orders,
                'total_discount' => (int) round((float) $r->total_discount),
            ])->all();
    }

    // ─── Service Report (Module 14) ───────────────────────────────────

    public function getServiceReport(int $branchId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $range = $this->resolveRange('daily', $dateFrom, $dateTo);

        $topServices = $this->buildTopServicesReport($branchId, $range['from'], $range['to']);
        $categoryRevenue = $this->buildCategoryRevenueReport($branchId, $range['from'], $range['to']);

        return [
            'date_from' => $range['from']->toDateString(),
            'date_to' => $range['to']->toDateString(),
            'top_services' => $topServices,
            'category_revenue' => $categoryRevenue,
        ];
    }

    private function buildTopServicesReport(int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('services as s', 's.id', '=', 'oi.item_id')
            ->where('o.branch_id', $branchId)
            ->where('oi.item_type', 'service')
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $query = $this->applyPaidScope($query, 'o');

        return $query
            ->selectRaw('s.id, s.name, SUM(oi.qty) as total_qty, SUM(oi.qty * oi.unit_price) as total_revenue')
            ->groupBy('s.id', 's.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'total_qty' => (int) $r->total_qty,
                'total_revenue' => (int) round((float) $r->total_revenue),
            ])->all();
    }

    private function buildCategoryRevenueReport(int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('services as s', 's.id', '=', 'oi.item_id')
            ->leftJoin('service_categories as sc', 'sc.id', '=', 's.category_id')
            ->where('o.branch_id', $branchId)
            ->where('oi.item_type', 'service')
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $query = $this->applyPaidScope($query, 'o');

        return $query
            ->selectRaw("COALESCE(sc.name, 'ไม่มีหมวดหมู่') as category_name, SUM(oi.qty * oi.unit_price) as total_revenue, SUM(oi.qty) as total_qty")
            ->groupBy('sc.id', 'sc.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn($r) => [
                'category_name' => (string) $r->category_name,
                'total_revenue' => (int) round((float) $r->total_revenue),
                'total_qty' => (int) $r->total_qty,
            ])->all();
    }

    // ─── Masseuse Report (Module 15) ──────────────────────────────────

    public function getMasseuseReport(int $branchId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $range = $this->resolveRange('daily', $dateFrom, $dateTo);

        return [
            'date_from' => $range['from']->toDateString(),
            'date_to' => $range['to']->toDateString(),
            'masseuses' => $this->buildMasseusePerformance($branchId, $range['from'], $range['to']),
        ];
    }

    private function buildMasseusePerformance(int $branchId, Carbon $from, Carbon $to): array
    {
        $revenueQuery = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('masseuses as m', 'm.id', '=', 'oi.masseuse_id')
            ->where('o.branch_id', $branchId)
            ->where('oi.item_type', 'service')
            ->whereNotNull('oi.masseuse_id')
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $revenueQuery = $this->applyPaidScope($revenueQuery, 'o');

        $revenues = $revenueQuery
            ->selectRaw(
                "oi.masseuse_id, " .
                "COALESCE(NULLIF(m.nickname, ''), m.full_name, CONCAT('Masseuse #', oi.masseuse_id)) as name, " .
                "SUM(oi.qty * oi.unit_price) as total_revenue, " .
                "COUNT(DISTINCT o.id) as queue_count, " .
                "SUM(oi.qty) as total_services"
            )
            ->groupBy('oi.masseuse_id', 'm.nickname', 'm.full_name')
            ->orderByDesc('total_revenue')
            ->get()
            ->keyBy('masseuse_id');

        $commissionQuery = DB::table('commissions as c')
            ->join('order_items as oi', 'oi.id', '=', 'c.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.branch_id', $branchId)
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $commissionQuery = $this->applyPaidScope($commissionQuery, 'o');

        $commissions = $commissionQuery
            ->selectRaw('c.masseuse_id, SUM(c.amount) as total_commission')
            ->groupBy('c.masseuse_id')
            ->get()
            ->keyBy('masseuse_id');

        $result = [];
        foreach ($revenues as $masseuseId => $rev) {
            $comm = $commissions->get($masseuseId);
            $result[] = [
                'masseuse_id' => (int) $masseuseId,
                'name' => (string) $rev->name,
                'total_revenue' => (int) round((float) $rev->total_revenue),
                'total_commission' => (int) round((float) ($comm->total_commission ?? 0)),
                'queue_count' => (int) $rev->queue_count,
                'total_services' => (int) $rev->total_services,
            ];
        }

        return $result;
    }

    // ─── Product Report (Module 16) ───────────────────────────────────

    public function getProductReport(int $branchId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $range = $this->resolveRange('daily', $dateFrom, $dateTo);

        return [
            'date_from' => $range['from']->toDateString(),
            'date_to' => $range['to']->toDateString(),
            'top_products' => $this->buildTopProducts($branchId, $range['from'], $range['to']),
            'stock_value' => $this->buildStockValue(),
        ];
    }

    private function buildTopProducts(int $branchId, Carbon $from, Carbon $to): array
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.item_id')
            ->where('o.branch_id', $branchId)
            ->where('oi.item_type', 'product')
            ->where('o.created_at', '>=', $from)
            ->where('o.created_at', '<', $to);

        $query = $this->applyPaidScope($query, 'o');

        return $query
            ->selectRaw('p.id, p.name, p.sku, SUM(oi.qty) as total_qty, SUM(oi.qty * oi.unit_price) as total_revenue')
            ->groupBy('p.id', 'p.name', 'p.sku')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(fn($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'sku' => (string) ($r->sku ?? '-'),
                'total_qty' => (int) $r->total_qty,
                'total_revenue' => (int) round((float) $r->total_revenue),
            ])->all();
    }

    private function buildStockValue(): array
    {
        return DB::table('products')
            ->where('is_active', 1)
            ->orderByDesc(DB::raw('stock_qty * cost_price'))
            ->get(['id', 'name', 'sku', 'stock_qty', 'cost_price', 'sell_price'])
            ->map(fn($r) => [
                'id' => (int) $r->id,
                'name' => (string) $r->name,
                'sku' => (string) ($r->sku ?? '-'),
                'stock_qty' => (int) $r->stock_qty,
                'cost_price' => (float) $r->cost_price,
                'stock_value' => (int) round((float) $r->cost_price * (int) $r->stock_qty),
            ])->all();
    }

    // ─── CSV Export ───────────────────────────────────────────────────

    public function exportSalesCsv(int $branchId, string $period, ?string $dateFrom, ?string $dateTo): array
    {
        $report = $this->getSalesReport($branchId, $period, $dateFrom, $dateTo);
        $header = ['ช่วงเวลา', 'ยอดขาย (฿)', 'จำนวนออเดอร์', 'ส่วนลด (฿)'];
        $rows = array_map(fn($r) => [$r['period'], $r['total_sales'], $r['total_orders'], $r['total_discount']], $report['data']);
        return ['header' => $header, 'rows' => $rows, 'filename' => 'sales_report_' . now()->format('Ymd') . '.csv'];
    }

    public function exportServiceCsv(int $branchId, ?string $dateFrom, ?string $dateTo): array
    {
        $report = $this->getServiceReport($branchId, $dateFrom, $dateTo);
        $header = ['ชื่อบริการ', 'จำนวน (ครั้ง)', 'รายได้ (฿)'];
        $rows = array_map(fn($r) => [$r['name'], $r['total_qty'], $r['total_revenue']], $report['top_services']);
        return ['header' => $header, 'rows' => $rows, 'filename' => 'service_report_' . now()->format('Ymd') . '.csv'];
    }

    public function exportMasseuseCsv(int $branchId, ?string $dateFrom, ?string $dateTo): array
    {
        $report = $this->getMasseuseReport($branchId, $dateFrom, $dateTo);
        $header = ['ชื่อหมอนวด', 'รายได้ (฿)', 'ค่าคอมมิชชัน (฿)', 'จำนวนรอบ', 'จำนวนบริการ'];
        $rows = array_map(fn($r) => [$r['name'], $r['total_revenue'], $r['total_commission'], $r['queue_count'], $r['total_services']], $report['masseuses']);
        return ['header' => $header, 'rows' => $rows, 'filename' => 'masseuse_report_' . now()->format('Ymd') . '.csv'];
    }

    public function exportProductCsv(int $branchId, ?string $dateFrom, ?string $dateTo): array
    {
        $report = $this->getProductReport($branchId, $dateFrom, $dateTo);
        $header = ['ชื่อสินค้า', 'SKU', 'จำนวนขาย', 'รายได้ (฿)'];
        $rows = array_map(fn($r) => [$r['name'], $r['sku'], $r['total_qty'], $r['total_revenue']], $report['top_products']);

        // Append stock value section
        $rows[] = [''];
        $rows[] = ['--- มูลค่าสต็อกคงเหลือ ---', '', '', ''];
        $stockHeader = ['ชื่อสินค้า', 'SKU', 'คงเหลือ', 'มูลค่า (฿)'];
        $rows[] = $stockHeader;
        foreach ($report['stock_value'] as $sv) {
            $rows[] = [$sv['name'], $sv['sku'], $sv['stock_qty'], $sv['stock_value']];
        }

        return ['header' => $header, 'rows' => $rows, 'filename' => 'product_report_' . now()->format('Ymd') . '.csv'];
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function resolveRange(string $period, ?string $dateFrom, ?string $dateTo): array
    {
        if ($dateFrom && $dateTo) {
            return [
                'from' => Carbon::parse($dateFrom)->startOfDay(),
                'to' => Carbon::parse($dateTo)->endOfDay()->addSecond(),
            ];
        }

        $now = Carbon::now();

        return match ($period) {
            'yearly' => [
                'from' => $now->copy()->subYears(2)->startOfYear(),
                'to' => $now->copy()->endOfDay()->addSecond(),
            ],
            'monthly' => [
                'from' => $now->copy()->subMonths(11)->startOfMonth(),
                'to' => $now->copy()->endOfDay()->addSecond(),
            ],
            default => [
                'from' => $now->copy()->subDays(29)->startOfDay(),
                'to' => $now->copy()->endOfDay()->addSecond(),
            ],
        };
    }

    private function baseOrderQuery(int $branchId)
    {
        $query = DB::table('orders as o')
            ->where('o.branch_id', $branchId);

        return $this->applyPaidScope($query, 'o');
    }

    private function applyPaidScope($query, string $alias)
    {
        return $query->where(function ($q) use ($alias) {
            $q->where($alias . '.status', 'paid')
              ->orWhereNull($alias . '.status');
        });
    }

    public function resolveBranchId(?int $branchId): int
    {
        if ($branchId !== null && $branchId > 0) {
            $branch = DB::table('branches')->where('id', $branchId)->where('is_active', 1)->first();
            if ($branch) {
                return (int) $branch->id;
            }
        }

        $branch = DB::table('branches')->where('is_active', 1)->orderBy('id')->first();
        return $branch ? (int) $branch->id : 1;
    }
}
