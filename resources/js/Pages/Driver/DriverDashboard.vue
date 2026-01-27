<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    auth: Object,
    pendingDispatches: { type: Array, default: () => [] },
    // 単数から複数(Array)に変更。Controllerの修正に合わせています
    activeAssignments: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({ success: null, error: null }) }
});

// フォームの初期化
const form = useForm({});
const page = usePage();

// 通知管理用の状態
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// 通知を表示する関数
const notify = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 5000);
}

/**
 * 目的地ごとにグループ化する計算プロパティ
 */
const groupedAssignments = computed(() => {
    return props.activeAssignments.reduce((acc, curr) => {
        const loc = curr.end_location || '目的地未設定';
        if (!acc[loc]) acc[loc] = [];
        acc[loc].push(curr);
        return acc;
    }, {});
});

/**
 * 受諾ボタンのハンドラー
 */
const handleAccept = (id) => {
    if (!id) return;
    if (confirm('この依頼を受諾しますか？')) {
        form.post(`/driver/dispatches/${id}/accept`, {
            preserveScroll: true,
            onSuccess: () => notify('依頼を受諾しました。安全運転でお願いします'),
        });
    }
};

const handleSingleComplete = (dispatch) => {
    if (!dispatch || !dispatch.id) return;

    navigator.geolocation.getCurrentPosition (
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;

            if (confirm(`${dispatch.user?.name}様の到着を報告しますか？`)) {
                form.post(route('driver.complete', {dispatch: dispatch.id}), {
                    preserveScroll: true,
                    onSuccess: () => notify('完了しました'),
                });
            }
        },
        (error) => {
            const userName = dispatch.user?.name || '利用者';
            notify('位置情報が取得できませんでした', 'error');

            if (confirm(`${userName} 様の報告をしますか？（位置情報なしで送信）`)) {
                form.post(route('driver.complete', { dispatch: dispatch.id }), {
                    preserveScroll: true,
                    onSuccess: () => notify(`${userName} 様の報告をしました。`),
                });
            }
        }
    );
};

/**
 * 一括完了ボタンのハンドラー
 */
const handleBulkComplete = (location, dispatches) => {
    const ids = dispatches.map(d => d.id);
    if (confirm(`「${location}」へ到着した ${ids.length} 名の送迎を完了しますか？`)) {
        form.post(route('driver.dashboard.bulk-complete'), {
            // 送信するデータ
            dispatch_ids: ids
        }, {
            preserveScroll: true,
            onSuccess: () => notify('一括完了を報告しました。お疲れ様でした！'),
            onError: () => notify('処理中にエラーが発生しました', 'error')
        });
    }
};

// サーバーサイドからのフラッシュメッセージを監視
watch(() => page.props.flash?.success, (newVal) => {
    if (newVal) notify(newVal, 'success');
});
watch(() => page.props.flash?.error, (newVal) => {
    if (newVal) notify(newVal, 'error');
});

const openMap = (location) => {
    if (!location) return;
    window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location)}`, '_blank');
};
</script>

<template>
    <Head title="ドライバーダッシュボード" />

    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-[-20px] opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="translate-x-[20px] opacity-0"
    >
        <div v-if="showToast" 
             class="fixed top-5 right-5 z-[100] flex items-center p-4 w-full max-w-xs rounded-2xl shadow-2xl border-2 backdrop-blur-md"
             :class="toastType === 'success' ? 'bg-green-50/90 text-green-800 border-green-200' : 'bg-red-50/90 text-red-800 border-red-200'">
            <div class="ml-3 text-sm font-black">{{ toastMessage }}</div>
            <button @click="showToast = false" class="ml-auto text-gray-400 hover:text-gray-900 px-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- 現在の任務（一括完了対応） -->
                <section>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2 text-slate-700">
                        <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                        対応中の任務（目的地別）
                    </h3>
                    
                    <div v-if="Object.keys(groupedAssignments).length > 0" class="space-y-6">
                        <div v-for="(dispatches, location) in groupedAssignments" :key="location" 
                             class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden transition-all hover:shadow-md">
                            
                            <!-- 目的地ヘッダー -->
                            <div class="bg-slate-50 px-8 py-4 border-b border-slate-100 flex justify-between items-center">
                                <div @click="openMap(location)" class="flex items-center gap-2 cursor-pointer group">
                                    <span class="text-blue-600 group-hover:scale-125 transition-transform">📍</span>
                                    <span class="font-black text-slate-800 underline decoration-blue-200 decoration-4 underline-offset-4">{{ location }}</span>
                                </div>
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-3 py-1 rounded-full uppercase">
                                    {{ dispatches.length }}名 走行中
                                </span>
                            </div>

                            <!-- 利用者リスト -->
                            <div class="p-8 space-y-4">
                                <div v-for="dispatch in dispatches" :key="dispatch.id" class="flex justify-between items-center bg-slate-50/50 p-4 rounded-xl border border-slate-100">
                                    <div>
                                        <p class="text-lg font-black text-slate-900">{{ dispatch.user?.name || '名称不明' }} 様</p>
                                        <p class="text-[10px] text-slate-400 font-bold mt-1">
                                            乗車場所: {{ dispatch.start_location }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-mono text-slate-400">#{{ dispatch.id }}</p>
                                    </div>
                                </div>
                                
                                <button 
                                    @click="handleBulkComplete(location, dispatches)"
                                    :disabled="form.processing"
                                    class="w-full mt-4 py-5 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3 text-lg shadow-xl"
                                >
                                    <svg v-if="form.processing" class="animate-spin h-6 w-6 text-white" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>{{ form.processing ? '処理中...' : `「${location}」到着完了を報告` }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">現在、担当中の依頼はありません</p>
                    </div>
                </section>

                <!-- 新着依頼リスト（変更なし） -->
                <section>
                    <h3 class="text-lg font-bold mb-4 text-slate-700">新着依頼</h3>
                    <div v-if="props.pendingDispatches?.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="dispatch in props.pendingDispatches" :key="dispatch.id" class="bg-white rounded-[1.5rem] p-6 shadow-sm border border-slate-200 flex flex-col group hover:shadow-lg transition-all hover:-translate-y-1">
                            <div class="mb-4">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">依頼 #{{ dispatch.id }}</p>
                                <p class="text-sm font-bold mt-2 leading-tight text-slate-800 italic">"{{ dispatch.start_location }}"</p>
                                <div class="flex justify-center my-2 text-slate-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 14l-7 7-7-7" stroke-width="3" stroke-linecap="round"/></svg>
                                </div>
                                <p class="text-sm font-bold leading-tight text-slate-500 italic">"{{ dispatch.end_location }}"</p>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-50 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-400">{{ dispatch.user?.name || '匿名ユーザー' }}</span>
                                <button 
                                    @click="handleAccept(dispatch.id)"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-xl text-xs font-black hover:bg-blue-700 disabled:bg-slate-100 disabled:text-slate-400 transition-all uppercase tracking-tighter shadow-md"
                                >
                                    受諾する
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200">
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">現在、新しい依頼はありません</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>