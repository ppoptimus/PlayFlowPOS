<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommissionService
{
    /**
     * คำนวณและบันทึกค่าคอมมิชชันสำหรับ Order ทั้งใบ
     */
    public function processOrderCommissions(int $orderId): void
    {
        // ดึงรายการใน Order ที่มีการระบุ "หมอนวด" (รองรับทั้ง service และ product)
        $items = DB::table('order_items')
            ->where('order_id', $orderId)
            ->whereNotNull('masseuse_id')
            ->get();

        foreach ($items as $item) {
            $this->calculateAndSave($item);
        }
    }

    /**
     * คำนวณรายรายการ (Item) และบันทึกลงฐานข้อมูล
     */
    private function calculateAndSave($item): void
    {
        // 1. ดึงการตั้งค่าคอมมิชชัน (ตรวจสอบทั้ง service_id และ product_id)
        $column = $item->item_type === 'service' ? 'service_id' : 'product_id';

        $config = DB::table('commission_configs')
            ->where('item_type', $item->item_type) 
            ->where('item_id', $item->item_id)
            ->first();

        if (!$config) return;

        $amount = 0;

        // 2. คำนวณตามประเภท (Fixed หรือ Percent)
        if ($config->type === 'fixed') {
            // แบบรายรอบ: (ค่าตอบแทนคงที่) x (จำนวนครั้งที่นวด)
            $amount = (float) $config->value * (int) $item->qty;
        } 
        else if ($config->type === 'percent') {
            // แบบเปอร์เซ็นต์: หักต้นทุนร้านก่อน (Deduct Cost) ตาม SRS
            $unitPrice = (float) $item->unit_price;
            $deductCost = (float) ($config->deduct_cost ?? 0);
            
            // ยอดที่เหลือหลังหักต้นทุน
            $baseForCommission = max(0, $unitPrice - $deductCost);
            
            // คำนวณเปอร์เซ็นต์ และคูณจำนวนรอบ
            $amount = ($baseForCommission * ((float) $config->value / 100)) * (int) $item->qty;
        }

        // 3. บันทึกผลลัพธ์ลงตาราง commissions (ใช้ updateOrInsert เพื่อป้องกันข้อมูลซ้ำ)
        DB::table('commissions')->updateOrInsert(
            ['order_item_id' => $item->id], // เงื่อนไขตรวจสอบ
            [
                'masseuse_id' => $item->masseuse_id,
                'amount' => round($amount, 2), // ปัดเศษ 2 ตำแหน่งตามมาตรฐานบัญชี
                'calculated_at' => Carbon::now()
            ]
        );
    }
}