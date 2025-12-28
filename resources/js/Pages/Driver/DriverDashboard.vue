<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

/**
 * プロパティの定義 (Laravelのコントローラーから渡されるデータ)
 */
const props = defineProps({
    auth: Object,
    // 未割り当ての依頼リスト
    pendingDispatches: {
        type: Array,
        default: () => []
    },
    // 現在自分が担当している依頼 (1件)
    activeAssignment: {
        type: Object,
        default: null
    }
});

/**
 * フォーム管理
 * Inertiaの useForm を使うことで送信中のローディング状態などを簡単に扱えます
 */
const form = useForm({});

/**
 * 日時を「12月26日 14:00」のような形式に変換する関数
 */
const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return '未設定';
    const date = new Date(dateTimeString);
    return date.toLocaleString('ja-JP', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

/**
 * 配車依頼を引き受ける処理
 */
const handleAccept = (dispatchId) => {
    if (confirm('この配車依頼を引き受けますか？')) {
        // Laravel側のルート 'driver.dispatches.accept' へPOST送信
        form.post(route('driver.dispatches.accept', dispatchId), {
            preserveScroll: true,
            onSuccess: () => {
                // 成功時の処理（通知など）
            },
        });
    }
};

/**
 * 配送完了を報告する処理
 */
const handleComplete = (assignmentId) => {
    if (confirm('配送完了を報告しますか？')) {
        form.post(route('driver.dispatches.complete', assignmentId), {
            preserveScroll: true,
        });
    }
};

/**
 * 依頼の緊急度などを判定する（デモ用ロジック）
 */
const getPriorityClass = (dateTime) => {
    const now = new Date();
    const limit = new Date(dateTime);
    const diff = (limit - now) / (1000 * 60 * 60); // 残り時間（時間単位）

    if (diff < 1) return 'bg-red-100 text-red-800 border-red-200'; // 1時間以内は赤
    if (diff < 3) return 'bg-yellow-100 text-yellow-800 border-yellow-200'; // 3時間以内は黄
    return 'bg-green-100 text-green-800 border-green-200'; // それ以外は緑
};

</script>

<template>
    <Head title="ドライバーダッシュボード" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold leading-tight text-gray-800">
                    ドライバー・ダッシュボード
                </h2>
                <div class="text-sm text-gray-500">
                    ドライバー: {{ auth.user.name }}
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- 1. 現在の任務セクション (Active Assignment) -->
                <section>
                    <h3 class="flex items-center text-lg font-bold text-gray-900 mb-4">
                        <span class="mr-2">🚛</span> 現在対応中の任務
                    </h3>

                    <div v-if="activeAssignment" class="bg-white rounded-2xl shadow-sm border-2 border-blue-500 overflow-hidden">
                        <div class="bg-blue-500 px-6 py-2 flex justify-between items-center text-white">
                            <span class="text-sm font-bold">進行中</span>
                            <span class="text-xs">ID: #{{ activeAssignment.id }}</span>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- 配送詳細 -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">荷主・顧客</p>
                                        <p class="text-lg font-bold text-gray-900">{{ activeAssignment.user?.name || '不明' }}</p>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex flex-col items-center">
                                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                            <div class="w-0.5 h-8 bg-gray-200"></div>
                                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                        </div>
                                        <div class="space-y-4">
                                            <div>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase">出発地</p>
                                                <p class="text-sm font-medium">{{ activeAssignment.start_location }}</p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase">到着地</p>
                                                <p class="text-sm font-medium">{{ activeAssignment.end_location }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- アクションボタン -->
                                <div class="flex flex-col justify-end space-y-3">
                                    <div class="flex gap-2">
                                        <a :href="'tel:' + activeAssignment.user?.phone" class="flex-1 text-center py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition">
                                            電話連絡
                                        </a>
                                        <button class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm hover:bg-gray-200 transition">
                                            地図を表示
                                        </button>
                                    </div>
                                    <button 
                                        @click="handleComplete(activeAssignment.id)"
                                        :disabled="form.processing"
                                        class="w-full py-4 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition disabled:opacity-50"
                                    >
                                        配送完了を報告する
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center">
                        <p class="text-gray-500 font-medium">現在、実行中の任務はありません。</p>
                        <p class="text-xs text-gray-400 mt-1">下のリストから新しい依頼を引き受けてください。</p>
                    </div>
                </section>

                <!-- 2. 未割り当て依頼セクション (Pending Dispatches) -->
                <section>
                    <h3 class="flex items-center text-lg font-bold text-gray-900 mb-4">
                        <span class="mr-2">📋</span> 新着・待機中の依頼
                    </h3>

                    <div v-if="pendingDispatches.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="dispatch in pendingDispatches" :key="dispatch.id" 
                             class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                            
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-4">
                                    <div :class="['px-2.5 py-1 rounded text-[10px] font-bold border', getPriorityClass(dispatch.requested_pickup_datetime)]">
                                        {{ formatDateTime(dispatch.requested_pickup_datetime) }} 締切
                                    </div>
                                    <span class="text-xs font-bold text-gray-400">#{{ dispatch.id }}</span>
                                </div>

                                <div class="space-y-3 mb-6">
                                    <div class="flex items-start gap-2">
                                        <span class="text-blue-500 font-bold text-xs mt-0.5">自</span>
                                        <p class="text-sm text-gray-700 leading-snug">{{ dispatch.start_location }}</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="text-red-500 font-bold text-xs mt-0.5">至</span>
                                        <p class="text-sm text-gray-700 leading-snug">{{ dispatch.end_location }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                                    <div class="text-xs text-gray-500">
                                        荷主: <span class="font-bold text-gray-700">{{ dispatch.user?.name }}</span>
                                    </div>
                                    <button 
                                        @click="handleAccept(dispatch.id)"
                                        :disabled="form.processing || activeAssignment"
                                        class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                                    >
                                        引き受ける
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="bg-white rounded-xl p-12 text-center border shadow-sm">
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-full mb-4">
                            <span class="text-xl">💤</span>
                        </div>
                        <p class="text-gray-500">現在、新しい配車依頼はありません。</p>
                    </div>
                </section>

            </div>
        </div>

        <!-- 簡易フッターナビ（スマホ用） -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t sm:hidden px-6 py-3 flex justify-around">
            <button class="flex flex-col items-center text-blue-600">
                <span class="text-xl">🏠</span>
                <span class="text-[10px] font-bold">ホーム</span>
            </button>
            <button class="flex flex-col items-center text-gray-400">
                <span class="text-xl">📋</span>
                <span class="text-[10px] font-bold">履歴</span>
            </button>
            <button class="flex flex-col items-center text-gray-400">
                <span class="text-xl">👤</span>
                <span class="text-[10px] font-bold">マイページ</span>
            </button>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* 必要に応じて個別のスタイルを追加 */
</style>