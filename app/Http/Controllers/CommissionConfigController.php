<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionConfigController extends Controller
{
    /**
     * แสดงเฉพาะรายการที่ตั้งค่าคอมมิชชันไว้แล้ว
     */
    public function index()
    {
        // ดึงข้อมูลการตั้งค่าคอมมิชชัน และเชื่อมชื่อจากตารางต่างๆ ตาม item_type
        $configs = DB::table('commission_configs as cc')
            ->select('cc.*', 
                DB::raw("CASE 
                    WHEN cc.item_type = 'service' THEN (SELECT name FROM services WHERE id = cc.item_id)
                    WHEN cc.item_type = 'product' THEN (SELECT name FROM products WHERE id = cc.item_id)
                    WHEN cc.item_type = 'package' THEN (SELECT name FROM packages WHERE id = cc.item_id)
                END as item_name")
            )
            ->get();

        // ดึงรายการทั้งหมดเพื่อใช้ในการเลือกเวลา "เพิ่มใหม่"
        $availableServices = DB::table('services')->select('id', 'name')->get();
        $availableProducts = DB::table('products')->where('type', 'retail')->select('id', 'name')->get();
        $availablePackages = DB::table('packages')->select('id', 'name')->get();

        return view('admin.commission.index', compact('configs', 'availableServices', 'availableProducts', 'availablePackages'));
    }

    /**
     * เพิ่มหรืออัปเดตค่าคอมมิชชัน
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_type' => 'required|in:service,product,package',
            'item_id' => 'required|integer',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'deduct_cost' => 'nullable|numeric|min:0',
        ]);

        DB::table('commission_configs')->updateOrInsert(
            ['item_type' => $request->item_type, 'item_id' => $request->item_id],
            [
                'type' => $request->type,
                'value' => $request->value,
                'deduct_cost' => $request->deduct_cost ?? 0,
            ]
        );

        return back()->with('success', 'บันทึกการตั้งค่าคอมมิชชันเรียบร้อยแล้ว');
    }

    /**
     * ลบการตั้งค่าคอมมิชชัน
     */
    public function destroy($id)
    {
        DB::table('commission_configs')->where('id', $id)->delete();
        return back()->with('success', 'ลบรายการค่าคอมมิชชันแล้ว');
    }
}