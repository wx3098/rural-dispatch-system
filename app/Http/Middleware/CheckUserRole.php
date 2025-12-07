<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * ユーザーが指定されたロールを持っているかを確認するミドルウェア
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  このルートにアクセスを許可するロール名 (例: 'admin', 'driver', 'user')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. 認証されていない、またはロールが一致しない場合（不正アクセス）をチェック
        if (!Auth::check() || Auth::user()->role !== $role) {
            
            // 2. 認証済みであれば、そのユーザーの正しいホームパスにリダイレクトする
            if (Auth::check()) {
                
                $currentUserRole = Auth::user()->role;
                
                // ユーザーのロールに基づき、適切なホーム画面へリダイレクト
                switch ($currentUserRole) {
                    case 'admin':
                        return redirect(route('admin.dashboard'));
                    case 'driver':
                        return redirect(route('driver.dashboard'));
                    case 'user':
                    default:
                        // 'user.dispatch.form' は routes/web.php で定義される一般ユーザーのホーム
                        return redirect(route('user.dispatch.form')); 
                }
            }
            
            // 3. 未認証ならログインページへリダイレクト
            return redirect(route('login'));
        }

        // 4. ロールが一致し、アクセスが許可された場合、次の処理へリクエストを渡す
        return $next($request);
    }
}
