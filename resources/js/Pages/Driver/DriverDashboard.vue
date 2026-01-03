<script setup>
import { ref,watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    auth: Object,
    pendingDispatches: { type: Array, default: () => [] },
    activeAssignment: { type: Object, default: null },
    flash: { type: Object, default: () => ({ success: null, error: null }) }
});

// フォームの初期化
const form = useForm({});
const page = usePage();

//通知管理用の状態
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

//通知を表示する関数
const notify = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 5000);
}
/**
 * 受諾ボタンのハンドラー
 */
const handleAccept = (id) => {
    if (!id) return;
    
    if (confirm('この依頼を受諾しますか？')) {
        form.post(`/driver/dispatches/${id}/accept`, {
            preserveScroll: true,
            onSuccess: () => {
                notify('依頼を受諾しました。安全運転でお願いします');
            },
            onError: (errors) => {
                notify('受諾処理中にエラーが発生しました', 'errors');
            }
        });
    }
};

/**
 * 完了ボタンのハンドラー
 * こちらも直接URL（/driver/dispatches/{id}/complete）を指定します
 */
const handleComplete = (id) => {
    if (!id) return;

    if (confirm('配送完了を報告しますか？')) {
        // 直接パスを指定してPOSTします
        form.post(`/driver/dispatches/${id}/complete`, {
            preserveScroll: true,
            onSuccess: () => {
                notify('配送完了を報告しました。お疲れ様でした！');
            },
            onError: (errors) => {
                notify('完了報告中にエラーが発生しました。', 'error');
            }
        });
    }
};

// サーバーサイドからのフラッシュメッセージ（Session::flash）を監視
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
            <button @click="showToast = false" class="ml-auto text-gray-400 hover:text-gray-900 px-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen font-sans">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- 現在の任務 -->
                <section v-if="props.activeAssignment">
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2 text-slate-700">
                        <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
                        対応中の任務
                    </h3>
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 transition-all hover:shadow-md">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mb-1">依頼主</p>
                                <p class="text-2xl font-black text-slate-900">{{ props.activeAssignment.user?.name || '名称不明' }} 様</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">依頼ID</p>
                                <p class="text-xs font-mono bg-slate-100 px-2 py-1 rounded">#{{ props.activeAssignment.id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div @click="openMap(props.activeAssignment.start_location)" class="p-5 bg-slate-50 rounded-2xl cursor-pointer hover:bg-blue-50 transition-all border-2 border-transparent hover:border-blue-100 group">
                                <p class="text-[10px] text-blue-600 font-black mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    出発地 (地図を表示)
                                </p>
                                <p class="text-md font-bold text-slate-700 leading-snug">{{ props.activeAssignment.start_location }}</p>
                            </div>
                            <div @click="openMap(props.activeAssignment.end_location)" class="p-5 bg-slate-50 rounded-2xl cursor-pointer hover:bg-rose-50 transition-all border-2 border-transparent hover:border-rose-100 group">
                                <p class="text-[10px] text-rose-600 font-black mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    到着地 (地図を表示)
                                </p>
                                <p class="text-md font-bold text-slate-700 leading-snug">{{ props.activeAssignment.end_location }}</p>
                            </div>
                        </div>

                        <button 
                            @click="handleComplete(props.activeAssignment.id)"
                            :disabled="form.processing"
                            class="w-full py-5 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3 text-lg"
                        >
                            <svg v-if="form.processing" class="animate-spin h-6 w-6 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? '報告を送信中...' : '配送完了を報告' }}</span>
                        </button>
                    </div>
                </section>

                <!-- 新着依頼リスト -->
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
                                    :disabled="form.processing || props.activeAssignment"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-xl text-xs font-black hover:bg-blue-700 disabled:bg-slate-100 disabled:text-slate-400 transition-all uppercase tracking-tighter"
                                >
                                    受諾する
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200">
                        <div class="inline-flex p-4 rounded-full bg-slate-50 text-slate-300 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">現在、新しい依頼はありません</p>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>