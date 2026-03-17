<?php

namespace App\Http\Controllers;

use App\Services\MembershipLevelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MembershipLevelController extends Controller
{
    private MembershipLevelService $membershipLevelService;

    public function __construct(MembershipLevelService $membershipLevelService)
    {
        $this->membershipLevelService = $membershipLevelService;
    }

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $pageData = $this->membershipLevelService->getPageData($search);

        return view('membership-levels.index', $pageData);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_spend' => ['required', 'numeric', 'min:0'],
        ]);

        $this->membershipLevelService->createTier($payload);

        return redirect()
            ->route('membership-levels')
            ->with('success', 'เพิ่มระดับสมาชิกเรียบร้อยแล้ว');
    }

    public function update(Request $request, int $tierId): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_spend' => ['required', 'numeric', 'min:0'],
        ]);

        $this->membershipLevelService->updateTier($tierId, $payload);

        return redirect()
            ->route('membership-levels')
            ->with('success', 'อัปเดตระดับสมาชิกเรียบร้อยแล้ว');
    }
}