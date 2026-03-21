<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\ShopContextService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    private ShopContextService $shopContext;

    public function __construct(ShopContextService $shopContext)
    {
        $this->shopContext = $shopContext;
        $this->middleware('guest')->except('logout');
    }

    public function username(): string
    {
        return 'username';
    }

    protected function authenticated(Request $request, $user)
    {
        $accessState = $this->shopContext->getLoginAccessState($user);
        if (!$accessState['allowed']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['username' => $accessState['message']]);
        }

        $user->last_login = now();
        $user->save();

        if ((string) ($user->role ?? '') === 'super_admin') {
            $this->shopContext->clearActiveShop();

            return redirect()->route('system.shops.index');
        }

        if ((string) ($user->role ?? '') === 'shop_owner') {
            return redirect()->route('branches.index');
        }

        if ((string) ($user->role ?? '') === 'masseuse') {
            return redirect()->route('masseuse.self');
        }

        return redirect()->route('dashboard');
    }
}
