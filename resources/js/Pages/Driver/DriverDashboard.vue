<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

/**
 * 1. Props Definition
 * Standard JavaScript object format for Vue 3 defineProps to avoid syntax errors.
 */
const props = defineProps({
    auth: Object,
    // For Driver
    pendingDispatches: { type: Array, default: () => [] },
    activeAssignments: { type: Array, default: () => [] },
    // For User
    myDispatches: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({ success: null, error: null }) }
});

const page = usePage();
const userRole = computed(() => props.auth?.user?.role || 'user');

/**
 * 2. Form for Booking (General User)
 */
const bookingForm = useForm({
    pickup_location: '',
    end_location: '',
    latitude: null,
    longitude: null
});

/**
 * 3. Form for Driver Actions
 */
const driverForm = useForm({
    dispatch_ids: []
});

/**
 * 4. UI States
 */
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
const showMapModal = ref(false);
const mapMode = ref('view'); // 'view', 'pickup', 'destination'
const mapRef = ref(null);

const notify = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    setTimeout(() => { showToast.value = false; }, 5000);
};

/**
 * 5. Map Logic (Selection & Geolocation)
 */
const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        notify('お使いのブラウザは位置情報に対応していません', 'error');
        return;
    }
    navigator.geolocation.getCurrentPosition((position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        // Mocking address conversion for demo
        bookingForm.pickup_location = `現在地 (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
        bookingForm.latitude = lat;
        bookingForm.longitude = lng;
        notify('現在地を取得しました');
    }, () => {
        notify('位置情報の取得に失敗しました', 'error');
    });
};

const openMapForSelection = (mode) => {
    mapMode.value = mode;
    showMapModal.value = true;
};

const confirmSelection = () => {
    if (mapMode.value === 'pickup') {
        bookingForm.pickup_location = "地図で指定した乗車場所";
    } else if (mapMode.value === 'destination') {
        bookingForm.end_location = "地図で指定した目的地";
    }
    showMapModal.value = false;
};

/**
 * 6. Driver Logic (Acceptance & Completion)
 */
const handleAccept = (id) => {
    if (!confirm('この依頼を引き受けますか？')) return;
    driverForm.post(`/driver/dispatches/${id}/accept`, {
        onSuccess: () => notify('依頼を受諾しました')
    });
};

const handleBulkComplete = (location, dispatches) => {
    const ids = dispatches.map(d => d.id);
    if (!confirm(`「${location}」への到着を完了しますか？`)) return;
    driverForm.dispatch_ids = ids;
    driverForm.post(route('driver.dashboard.bulk-complete'), {
        onSuccess: () => {
            notify('完了報告を送信しました');
            showMapModal.value = false;
        }
    });
};

// Grouping logic for drivers
const groupedAssignments = computed(() => {
    return (props.activeAssignments || []).reduce((acc, curr) => {
        const loc = curr.end_location || '未設定';
        if (!acc[loc]) acc[loc] = [];
        acc[loc].push(curr);
        return acc;
    }, {});
});

watch(() => page.props.flash?.success, (val) => val && notify(val));
watch(() => page.props.flash?.error, (val) => val && notify(val, 'error'));
</script>

<template>
    <Head title="送迎管理システム" />

    <!-- Notifications -->
    <Transition name="toast">
        <div v-if="showToast" class="fixed top-5 right-5 z-[100] p-4 rounded-2xl shadow-2xl border-2 bg-white flex items-center gap-3">
            <span :class="toastType === 'success' ? 'text-green-500' : 'text-red-500'">●</span>
            <div class="text-sm font-bold text-slate-800">{{ toastMessage }}</div>
        </div>
    </Transition>

    <AuthenticatedLayout>
        <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 space-y-10">
                
                <!-- ================= DRIVER DASHBOARD ================= -->
                <template v-if="userRole === 'driver'">
                    <!-- New Requests Section -->
                    <section>
                        <h3 class="text-xl font-black mb-4 flex items-center gap-2">
                            <span class="flex h-3 w-3 rounded-full bg-red-500 animate-ping"></span>
                            待機中の依頼（新規）
                        </h3>
                        <div v-if="props.pendingDispatches && props.pendingDispatches.length > 0" class="grid gap-4">
                            <div v-for="req in props.pendingDispatches" :key="req.id" class="bg-white p-6 rounded-3xl border shadow-sm flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-lg">{{ req.user?.name }} 様</div>
                                    <div class="text-sm text-slate-500">
                                        <span class="font-bold text-blue-600">乗車:</span> {{ req.pickup_location }} <br>
                                        <span class="font-bold text-orange-600">降車:</span> {{ req.end_location }}
                                    </div>
                                </div>
                                <button @click="handleAccept(req.id)" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-blue-700 transition-transform active:scale-95 shadow-lg">
                                    受諾する
                                </button>
                            </div>
                        </div>
                        <div v-else class="bg-slate-200/50 border-2 border-dashed border-slate-300 p-10 rounded-3xl text-center text-slate-500 font-bold">
                            現在、新しい依頼はありません
                        </div>
                    </section>

                    <!-- Active Tasks Section -->
                    <section v-if="Object.keys(groupedAssignments).length > 0">
                        <h3 class="text-xl font-black mb-4 flex items-center gap-2 text-slate-700">
                            <span class="h-3 w-3 rounded-full bg-blue-600"></span>
                            対応中の送迎
                        </h3>
                        <div class="space-y-6">
                            <div v-for="(dispatches, location) in groupedAssignments" :key="location" class="bg-white rounded-[2rem] shadow-md border border-slate-200 overflow-hidden">
                                <div class="bg-slate-900 text-white px-8 py-4 flex justify-between items-center">
                                    <span class="font-bold">目的地: {{ location }}</span>
                                    <span class="text-xs bg-white/20 px-2 py-1 rounded">{{ dispatches.length }}名</span>
                                </div>
                                <div class="p-6 space-y-3">
                                    <div v-for="d in dispatches" :key="d.id" class="p-3 bg-slate-50 rounded-xl border flex justify-between">
                                        <span class="font-bold">{{ d.user?.name }} 様</span>
                                        <span class="text-slate-400 text-xs">#{{ d.id }}</span>
                                    </div>
                                    <button @click="handleBulkComplete(location, dispatches)" class="w-full mt-4 bg-blue-600 text-white py-4 rounded-2xl font-black shadow-lg">
                                        到着を報告する
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </template>

                <!-- ================= USER DASHBOARD ================= -->
                <template v-else>
                    <section class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-100">
                        <h2 class="text-2xl font-black mb-8 text-slate-800">配車を予約する</h2>
                        
                        <div class="space-y-6">
                            <!-- Pickup Location -->
                            <div>
                                <label class="block text-sm font-bold text-slate-500 mb-2">どこから乗りますか？</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input v-model="bookingForm.pickup_location" type="text" class="w-full pl-10 pr-4 py-4 bg-slate-100 border-none rounded-2xl font-bold focus:ring-2 focus:ring-blue-500" placeholder="住所を入力するか地図で選択">
                                        <span class="absolute left-3 top-4">📍</span>
                                    </div>
                                    <button @click="getCurrentLocation" type="button" class="bg-white border-2 border-blue-600 text-blue-600 px-4 rounded-2xl font-bold hover:bg-blue-50">
                                        現在地
                                    </button>
                                </div>
                                <button @click="openMapForSelection('pickup')" type="button" class="mt-2 text-sm text-blue-600 font-bold underline">地図から細かく指定する</button>
                            </div>

                            <!-- Destination -->
                            <div>
                                <label class="block text-sm font-bold text-slate-500 mb-2">どこへ行きますか？</label>
                                <div class="relative">
                                    <input v-model="bookingForm.end_location" type="text" class="w-full pl-10 pr-4 py-4 bg-slate-100 border-none rounded-2xl font-bold focus:ring-2 focus:ring-orange-500" placeholder="目的地を入力">
                                    <span class="absolute left-3 top-4 text-orange-500">🏁</span>
                                </div>
                                <button @click="openMapForSelection('destination')" type="button" class="mt-2 text-sm text-orange-600 font-bold underline">地図で目的地を選ぶ</button>
                            </div>

                            <button :disabled="bookingForm.processing" class="w-full py-6 bg-slate-900 text-white rounded-[2rem] font-black text-xl shadow-2xl hover:bg-black transition-all active:scale-95 mt-4">
                                {{ bookingForm.processing ? '予約中...' : 'この内容で呼ぶ' }}
                            </button>
                        </div>
                    </section>

                    <!-- User Status -->
                    <section v-if="props.myDispatches && props.myDispatches.length > 0">
                        <h3 class="font-black text-slate-600 mb-4">現在のステータス</h3>
                        <div v-for="myReq in props.myDispatches" :key="myReq.id" class="bg-blue-50 border border-blue-100 p-6 rounded-3xl flex items-center justify-between mb-4">
                            <div>
                                <div class="font-black text-blue-800">{{ myReq.status === 'pending' ? 'ドライバーを探しています...' : 'ドライバーが向かっています' }}</div>
                                <div class="text-sm text-blue-600">{{ myReq.end_location }} 行き</div>
                            </div>
                            <div class="text-3xl animate-bounce">🚗</div>
                        </div>
                    </section>
                </template>

            </div>
        </div>

        <!-- Map Selection Modal -->
        <div v-if="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div class="bg-white w-full max-w-2xl rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col h-[80vh]">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="font-black text-xl">{{ mapMode === 'pickup' ? '乗車場所を指定' : '目的地を指定' }}</h2>
                    <button @click="showMapModal = false" class="text-2xl">×</button>
                </div>
                
                <div class="flex-1 bg-slate-200 relative">
                    <div class="absolute inset-0 flex items-center justify-center text-slate-400 font-bold italic text-center px-6">
                        地図をドラッグして<br>中心のピンを合わせます
                    </div>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="text-4xl mb-10 drop-shadow-lg">📍</div>
                    </div>
                </div>

                <div class="p-6 bg-white space-y-3">
                    <p class="text-xs text-slate-500 text-center font-bold">地図を動かして中心にピンを合わせてください</p>
                    <button @click="confirmSelection" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black">
                        この場所を{{ mapMode === 'pickup' ? '乗車地' : '目的地' }}にする
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

input::placeholder { color: #94a3b8; font-weight: normal; }
</style>