<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index(Request $request): View
    {
        $selectedCustomerId = $request->has('customer_id') ? (int) $request->query('customer_id') : null;
        $search = (string) $request->query('search', '');
        $pageData = $this->customerService->getPageData($request->user(), $selectedCustomerId, $search);

        return view('customers.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validateCustomerPayload($request);
        $customer = $this->customerService->createCustomer($payload);

        return redirect()
            ->route('customers', ['customer_id' => $customer['id']])
            ->with('success', 'เพิ่มลูกค้าใหม่เรียบร้อยแล้ว');
    }

    public function update(Request $request, int $customerId): RedirectResponse
    {
        $payload = $this->validateCustomerPayload($request, $customerId);
        $this->customerService->updateCustomer($customerId, $payload);

        return redirect()
            ->route('customers', ['customer_id' => $customerId])
            ->with('success', 'อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว');
    }

    public function destroy(int $customerId): RedirectResponse
    {
        $this->customerService->deleteCustomer($customerId);

        return redirect()
            ->route('customers')
            ->with('success', 'ลบข้อมูลลูกค้าเรียบร้อยแล้ว');
    }

    public function quickCreate(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'string',
                'max:32',
                'regex:/^[0-9\\-\\+\\s\\(\\)]+$/',
                Rule::unique('customers', 'phone'),
            ],
            'line_id' => ['nullable', 'string', 'max:120'],
        ]);

        $customer = $this->customerService->createCustomer($payload);

        return response()->json([
            'message' => 'บันทึกข้อมูลลูกค้าเรียบร้อยแล้ว',
            'customer' => [
                'id' => (int) $customer['id'],
                'name' => (string) $customer['name'],
                'phone' => (string) $customer['phone'],
                'line_id' => (string) ($customer['line_id'] ?? ''),
            ],
        ]);
    }

    public function history(int $customerId): JsonResponse
    {
        $customer = $this->customerService->getCustomerById($customerId);
        if ($customer === null) {
            return response()->json([
                'message' => 'ไม่พบข้อมูลลูกค้า',
            ], 404);
        }

        return response()->json([
            'customer' => $customer,
            'history' => $this->customerService->getHistoryByCustomerId($customerId),
        ]);
    }

    private function validateCustomerPayload(Request $request, ?int $customerId = null): array
    {
        $uniquePhoneRule = Rule::unique('customers', 'phone');
        if ($customerId !== null) {
            $uniquePhoneRule = $uniquePhoneRule->ignore($customerId);
        }

        $tierRules = ['nullable'];
        if (Schema::hasTable('membership_tiers')) {
            $tierRules = ['nullable', 'integer', 'exists:membership_tiers,id'];
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9\\-\\+\\s\\(\\)]+$/', $uniquePhoneRule],
            'line_id' => ['nullable', 'string', 'max:120'],
            'tier_id' => $tierRules,
            'preferred_pressure_level' => ['nullable', 'string', Rule::in(['light', 'medium', 'firm'])],
            'health_notes' => ['nullable', 'string', 'max:4000'],
            'contraindications' => ['nullable', 'string', 'max:4000'],
        ]);
    }
}
