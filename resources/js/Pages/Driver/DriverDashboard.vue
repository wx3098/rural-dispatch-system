<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// 1. propsの定義: サーバー(Laravel)から渡されるデータを受け取ります
const props = defineProps({
    auth: Object,
    pendingDispatches: { type: Array, default: () => [] },
    activeAssignments: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({ success: null, error: null }) }
});

// 2. Inertiaフォームの初期化: POST送信時に使用します
const form = useForm({
    dispatch_ids: [], // 一括処理用
    latitude: null,   // 位置情報用
    longitude: null   // 位置情報用
});
const page = usePage();

// 3. 通知(トースト)管理の状態
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// 4. 地図モーダル管理の状態
const showMapModal = ref(false);
const selectedLocation = ref('');

// 通知を表示する関数
const notify = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 5000);
}

// 5. 目的地ごとにデータをまとめる計算プロパティ
const groupedAssignments = computed(() => {
    return props.activeAssignments.reduce((acc, curr) => {
        const loc = curr.end_location || '目的地未設定';
        if (!acc[loc]) acc[loc] = [];
        acc[loc].push(curr);
        return acc;
    }, {});
});

// 6. 受諾ボタン: 依頼を自分の担当にする
const handleAccept = (id) => {
    if (!id) return;
    if (confirm('この依頼を受諾しますか？')) {
        form.post(`/driver/dispatches/${id}/accept`, {
            preserveScroll: true,
            onSuccess: () => notify('依頼を受諾しました'),
        });
    }
};

// 7. 地図をアプリ内で開く関数
const openMap = (location) => {
    if (!location) return;
    selectedLocation.value = location;
    showMapModal.value = true;
};

// 8. 到着報告（一括完了）
const handleBulkComplete = (location, dispatches) => {
    const ids = dispatches.map(d => d.id);
    if (confirm(`「${location}」へ到着した ${ids.length} 名の送迎を完了しますか？`)) {
        // form.dispatch_ids にID配列をセットしてから送信
        form.dispatch_ids = ids; 
        
        // 第2引数にデータを直接入れるのではなく、formのプロパティとして送信します
        form.post(route('driver.dashboard.bulk-complete'), {
            preserveScroll: true,
            onSuccess: () => {
                notify('一括完了を報告しました。');
                showMapModal.value = false; // 地図を開いていたら閉じる
            },
            onError: () => notify('処理中にエラーが発生しました', 'error')
        });
    }
};

// サーバーサイドからのメッセージを監視
watch(() => page.props.flash?.success, (newVal) => { if (newVal) notify(newVal, 'success'); });
watch(() => page.props.flash?.error, (newVal) => { if (newVal) notify(newVal, 'error'); });
</script>

<template>
    <Head title="ドライバーダッシュボード" />

    <!-- 通知トースト -->
    <Transition name="toast">
        <div v-if="showToast" class="fixed top-5 right-5 z-[100] p-4 rounded-2xl shadow-2xl border-2 bg-white flex items-center gap-3">
            <span :class="toastType === 'success' ? 'text-green-500' : 'text-red-500'">●</span>
            <div class="text-sm font-bold text-slate-800">{{ toastMessage }}</div>
        </div>
    </Transition>

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto px-4 space-y-8">
                
                <!-- 現在の任務セクション -->
                <section>
                    <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-600 rounded-full"></span>
                        運行中の任務（目的地別）
                    </h3>
                    
                    <div v-if="Object.keys(groupedAssignments).length > 0" class="space-y-6">
                        <div v-for="(dispatches, location) in groupedAssignments" :key="location" 
                             class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                            
                            <!-- ヘッダー兼地図ボタン -->
                            <div class="bg-slate-50 px-8 py-4 border-b flex justify-between items-center">
                                <button @click="openMap(location)" class="flex items-center gap-2 group">
                                    <span class="text-xl">📍</span>
                                    <span class="font-black text-blue-600 underline underline-offset-4">{{ location }}</span>
                                    <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded">地図を表示</span>
                                </button>
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-3 py-1 rounded-full">
                                    {{ dispatches.length }}名
                                </span>
                            </div>

                            <!-- 利用者リスト -->
                            <div class="p-8 space-y-4">
                                <div v-for="dispatch in dispatches" :key="dispatch.id" class="flex justify-between items-center bg-slate-50/50 p-4 rounded-xl border">
                                    <div class="font-bold text-slate-800">{{ dispatch.user?.name }} 様</div>
                                    <div class="text-xs text-slate-400">#{{ dispatch.id }}</div>
                                </div>
                                
                                <!-- 完了ボタン -->
                                <button 
                                    @click="handleBulkComplete(location, dispatches)"
                                    :disabled="form.processing"
                                    class="w-full mt-4 py-5 bg-slate-900 text-white rounded-2xl font-black hover:bg-black transition-all active:scale-95 disabled:opacity-50 text-lg shadow-xl"
                                >
                                    {{ form.processing ? '送信中...' : `「${location}」到着完了を報告` }}
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- 地図モーダル（アプリ内に留める工夫） -->
        <div v-if="showMapModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-3xl overflow-hidden shadow-2xl flex flex-col h-[85vh]">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="font-black text-xl text-slate-800">目的地: {{ selectedLocation }}</h2>
                    <button @click="showMapModal = false" class="text-slate-400 text-2xl font-bold">×</button>
                </div>
                
                <div class="flex-1 bg-slate-100">
                    <!-- iframeを使ってアプリ内で地図を表示 -->
                    <iframe
                        width="100%"
                        height="100%"
                        frameborder="0"
                        style="border:0"
                        :src="`https://maps.google.co.jp/maps?output=embed&q=${encodeURIComponent(selectedLocation)}`"
                        allowfullscreen
                    ></iframe>
                    <!-- ※APIキーがない場合は Google Maps Search URL (output=embed) を使用 -->
                </div>

                <div class="p-6">
                    <button @click="showMapModal = false" class="w-full bg-slate-800 text-white py-4 rounded-xl font-bold">
                        アプリのリストに戻る
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from { transform: translateY(-20px); opacity: 0; }
.toast-leave-to { transform: translateX(20px); opacity: 0; }
</style>