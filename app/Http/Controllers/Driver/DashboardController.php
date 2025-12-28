<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * ドライバーダッシュボードを表示する
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // 1. 保留中のリクエスト（まだ誰も引き受けていないもの）
        // driver_id が null かつ status が pending のものを取得
        $pendingDispatches = Dispatch::with('user:id,name')
            ->whereNull('driver_id')
            ->where('status', 'pending')
            ->orderBy('requested_pickup_datetime', 'asc')
            ->get();

        // 2. 自分が現在引き受けているリクエスト
        // ユーザー側の画面で名前を表示できるよう 'user' リレーションを含める
        $activeAssignment = Dispatch::with('user:id,name')
            ->where('driver_id', $user->id)
            ->whereIn('status', ['accepted', 'in_transit']) // Vue側のステータス判定と合わせる
            ->first();

        return Inertia::render('Driver/DriverDashboard', [
            'pendingDispatches' => $pendingDispatches,
            'activeAssignment' => $activeAssignment,
        ]);
    }

    /**
     * 配車依頼を引き受ける
     */
    public function accept(Dispatch $dispatch)
    {
        /** @var User $user */
        $user = Auth::user();

        // 二重引き受け防止
        if ($dispatch->driver_id !== null || $dispatch->status !== 'pending') {
            return back()->with('error', 'この依頼は既に他のドライバーが引き受けています。');
        }

        // ステータスを更新（Vue側の switch 文の判定に合わせる）
        $dispatch->update([
            'status' => 'accepted',
            'driver_id' => $user->id,
        ]);

        return redirect()->route('driver.dashboard')->with('success', '依頼を引き受けました。配送を開始してください。');
    }

    /**
     * 配送完了報告
     */
    public function complete(Dispatch $dispatch)
    {
        // 本人確認
        if ($dispatch->driver_id !== Auth::id()) {
            return abort(403);
        }

        $dispatch->update([
            'status' => 'completed',
        ]);

        return redirect()->route('driver.dashboard')->with('success', 'お疲れ様でした！配送完了を記録しました。');
    }
}