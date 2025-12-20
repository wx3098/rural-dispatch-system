<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\User; 
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    /**
     * 配車リクエストフォームを表示する。
     */
    public function form()
    {
        
        if (!Auth::check()) {
            return Redirect::route('login');
        }

        $user = Auth::user();

        $activeRequest = Dispatch::where('user_id', $user->id) 
            ->whereIn('status', ['pending', 'assigned'])
            ->first();

        // フォームコンポーネントをレンダリング
        return Inertia::render('User/DispatchRequestForm', [
            'activeRequest' => $activeRequest,
            'role' => $user->role ?? 'user', 
        ]);
    }

    /**
     * 新しい配車リクエストをデータベースに保存する。
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
             return Redirect::route('login');
        }

        $user = Auth::user();
        $userId = $user->id; 

        // 1. 同時にアクティブにできるリクエストは1つまで
        $activeRequestCount = Dispatch::where('user_id', $userId)
            ->whereIn('status', ['pending', 'assigned'])
            ->count();
        
        if ($activeRequestCount > 0) {
            // 既にアクティブリクエストがある場合、エラーメッセージとともにリダイレクト
            return Redirect::route('user.dispatch.form')->withErrors([
                'request_limit' => '現在、進行中のリクエストがあります。新しいリクエストは作成できません。',
            ]);
        }

        // 2. バリデーション
        $validated = $request->validate([
            'start_location' => ['required', 'string', 'max:255'],
            'end_location' => ['required', 'string', 'max:255'],
            // 日時は必須、日付形式であること、そして現在時刻よりも後であること
            'requested_pickup_datetime' => [
                'required', 
                'date', 
                'after:now' 
            ],
        ]);

        // 3. データベースに保存
        Dispatch::create([
            'user_id' => $userId,
            'start_location' => $validated['start_location'],
            'end_location' => $validated['end_location'],
            'requested_pickup_datetime' => $validated['requested_pickup_datetime'],
            'status' => 'pending', // 初期ステータスは「保留中」
        ]);

        // 4. 成功メッセージとともにフォーム画面に戻る
        return Redirect::route('user.dispatch.form')->with('success', '配車リクエストを送信しました。ドライバーの割り当てをお待ちください。');
    }
}