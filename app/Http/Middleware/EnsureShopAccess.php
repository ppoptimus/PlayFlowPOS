<?php

namespace App\Http\Middleware;

use App\Services\ShopContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureShopAccess
{
    private ShopContextService $shopContext;

    public function __construct(ShopContextService $shopContext)
    {
        $this->shopContext = $shopContext;
    }

    public function handle(Request $request, Closure $next)
    {
        $accessState = $this->shopContext->getLoginAccessState($request->user());

        if ($accessState['allowed']) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['username' => $accessState['message']]);
    }
}
