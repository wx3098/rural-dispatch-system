<script setup>
import { router } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';

// --- プレビュー環境用モック ---
const props = defineProps({
    activeRequest: { type: Object, default: null },
    role: { type: [Object, String], default: null }
});

const form = ref({
    start_location: '',
    end_location: '',
    requested_pickup_datetime: '',
    latitude: null,
    longitude: null,
    processing: false
});

const usePage = () => ({
    props: { flash: { success: null }, errors: {} }
});
// --- モック終了 ---

const showMapModal = ref(false);
const mapMode = ref('start');
const toast = ref({ show: false, message: '', type: 'success' });
const mapRef = ref(null);
let mapInstance = null;

const notify = (message, type = 'success') => {
    toast.value = { show: true, message, type };
    setTimeout(() => { toast.value.show = false; }, 3000);
};

const initMap = () => {
    if (!mapRef.value) return;
    const center = { lat: 33.8833, lng: 130.8833 };
    if (window.google) {
        mapInstance = new window.google.maps.Map(mapRef.value, {
            center: center,
            zoom: 16,
            disableDefaultUI: true,
            zoomControl: true,
        });
    } else {
        mapRef.value.innerHTML = `<div class="flex items-center justify-center h-full bg-gray-100 text-gray-400 font-bold p-8 text-center">Google Maps API 待機中</div>`;
    }
};

const openMap = (mode) => {
    if (isFormLocked.value) return;
    mapMode.value = mode;
    showMapModal.value = true;
    nextTick(() => { initMap(); });
};

const closeModal = () => {
    showMapModal.value = false;
};

const confirmMapSelection = () => {
    let lat = 33.8833, lng = 130.8833;
    if (mapInstance && window.google) {
        const center = mapInstance.getCenter();
        lat = center.lat();
        lng = center.lng();
    }
    const locationString = `地図指定 (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
    if (mapMode.value === 'start') {
        form.value.start_location = locationString;
        form.value.latitude = lat;
        form.value.longitude = lng;
    } else {
        form.value.end_location = locationString;
    }
    closeModal();
    notify('場所を確定しました');
};

const getCurrentLocation = () => {
    if (isFormLocked.value) return;
    if (!navigator.geolocation) {
        notify('位置情報非対応です', 'error');
        return;
    }
    notify('現在地を取得中...');
    navigator.geolocation.getCurrentPosition(
        (p) => {
            const lat = p.coords.latitude;
            const lng = p.coords.longitude;
            form.value.start_location = `現在地 (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
            form.value.latitude = lat;
            form.value.longitude = lng;
            notify('現在地を取得しました');
        },
        () => notify('取得に失敗しました', 'error')
    );
};

const submit = () => {
    form.value.processing = true;
    setTimeout(() => {
        form.value.processing = false;
        notify('配車依頼を送信しました');
        form.value.start_location = '';
        form.value.end_location = '';
    }, 1500);
};

//Geminiサジェスト
const suggestions = ref([]);
const isSuggesting = ref(false);

const fetchSuggestions = async () => {
    if (!form.value.end_location  ||  form.value.end_location.length < 2) {
        suggestions.value = [];
        return;
    }
    isSuggesting.value = true;
    try {
        const response = await fetch(window.location.origin + 'user/dispatch/suggest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ query: form.value.end_location})
        });
        const data = await response.json();
        suggestions.value = data.suggestions || [];
    } catch (e) {
        suggestions.value = [];
    } finally {
        isSuggesting.value = false;
    }
};

const selectSuggestion = (suggestion) => {
    form.value.end_location = suggestion;
    suggestions.value = [];
};

const isFormLocked = computed(() => !!(props.activeRequest && props.activeRequest?.status !== 'completed'));
const isDriverAssigned = computed(() => !!(props.activeRequest?.driver_id));
const currentStatusText = computed(() => {
    if (!props.activeRequest) return 'リクエストなし';
    return props.activeRequest.driver_id ? '配車確定' : 'マッチング中';
});
const roleDisplayName = computed(() => typeof props.role === 'object' ? props.role.name : (props.role || 'ユーザー'));
</script>

<template>
    <div class="min-h-screen bg-gray-50 text-gray-900 font-sans p-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-2xl rounded-[3rem] p-8 md:p-12 border border-gray-50">
                <div class="mb-10 flex justify-between items-start">
                    <div>
                        <h3 class="text-3xl font-black text-gray-800 tracking-tighter">どこへ行きますか？</h3>
                        <p class="text-[10px] font-black text-indigo-400 mt-2 uppercase tracking-[0.2em]">USER: {{ roleDisplayName }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div v-if="isFormLocked" class="bg-amber-100 text-amber-700 px-4 py-2 rounded-full text-[10px] font-black uppercase">進行中</div>
                        <button
                            type="button"
                            @click="router.post('/logout')"
                            class="text-[15px] font-black text-red-400 hover:tect-red-600 uppercase tracking-widest"
                        >
                            ログアウト
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- 出発地 -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-2">
                            <label class="text-xs font-black text-gray-400 uppercase tracking-widest">出発地</label>
                            <button type="button" @click="getCurrentLocation" :disabled="isFormLocked" class="text-xs font-black text-indigo-600 hover:opacity-70 disabled:text-gray-300">🛰 現在地を取得</button>
                        </div>
                        <div class="relative group">
                            <input 
                                v-model="form.start_location"
                                :disabled="isFormLocked"
                                class="w-full pl-6 pr-16 py-6 bg-gray-50 border-2 border-gray-100 rounded-[1.5rem] font-bold outline-none focus:border-indigo-300 transition-all"
                                placeholder="住所を入力するか右のアイコンをタップ"
                            />
                            <button 
                                type="button"
                                @click="openMap('start')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white shadow-sm border border-gray-100 rounded-xl hover:bg-indigo-50 transition-colors"
                            >
                                📍
                            </button>
                        </div>
                    </div>

                    <!-- 目的地 -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest px-2">目的地</label>
                        <div class="relative group">
                            <input 
                                v-model="form.end_location"
                                :disabled="isFormLocked"
                                @input="fetchSuggestions"
                                class="w-full pl-6 pr-16 py-6 bg-gray-50 border-2 border-gray-100 rounded-[1.5rem] font-bold outline-none focus:border-indigo-300 transition-all"
                                placeholder="目的地を入力(例：病院)"
                            />
                            <button 
                                type="button"
                                @click="openMap('end')"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white shadow-sm border border-gray-100 rounded-xl hover:bg-orange-50 transition-colors"
                            >
                                🏁
                            </button>
                        </div>
                        <!-- サジェスト -->
                        <div v-if="suggestions.length > 0" class="bg-white border-2 border-indigo-100 rounded-[1.5rem] overflow-hidden shadow-lg">
                            <div v-if="isSuggesting" class="px-6 py-4 text-sm text-gray-499 font-bold">検索中,,,</div>
                            <button
                                v-for="(s,i) in suggestions"
                                :key="i"
                                type="button"
                                @click="selectSuggestion(s)"
                                class="w-full text-left px-6 py-4 hover:bg-indigo-50 font-bold text-gray-700 border-b border-gray-50 last:border-0 transition-colors"
                            >
                                📍 {{ s }}
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 uppercase tracking-widest px-2">希望日時</label>
                        <input v-model="form.requested_pickup_datetime" :disabled="isFormLocked" type="datetime-local" class="w-full px-6 py-6 bg-gray-50 border-2 border-gray-100 rounded-[1.5rem] font-bold outline-none focus:border-indigo-300" />
                    </div>

                    <button type="submit" :disabled="isFormLocked || form.processing || !form.start_location" class="w-full py-6 bg-gray-900 text-white rounded-[2rem] font-black text-xl shadow-xl active:scale-95 transition-all disabled:bg-gray-200">
                        {{ isFormLocked ? '進行中のリクエストがあります' : '配車を依頼する' }}
                    </button>
                </form>

                <!-- アクティブなステータス -->
                <div v-if="props.activeRequest" class="mt-12 pt-10 border-t-2 border-dashed border-gray-100">
                    <div :class="isDriverAssigned ? 'bg-indigo-50 border-indigo-100' : 'bg-amber-50 border-amber-100'" class="p-8 rounded-[2.5rem] border-2">
                        <p class="font-black text-2xl mb-2" :class="isDriverAssigned ? 'text-indigo-900' : 'text-amber-900'">{{ currentStatusText }}</p>
                        <p class="text-sm font-bold text-gray-500">ドライバー: {{ props.activeRequest?.driver?.name || '探索中' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 地図モーダル -->
        <div v-if="showMapModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md">
            <div class="bg-white w-full max-w-2xl rounded-[3rem] overflow-hidden shadow-2xl flex flex-col h-[75vh]">
                <div class="p-8 border-b flex justify-between items-center bg-white">
                    <h2 class="font-black text-2xl text-gray-800">{{ mapMode === 'start' ? '乗車地' : '目的地' }}をピンで指定</h2>
                    <button @click="closeModal" class="text-4xl text-gray-300 hover:text-gray-900">&times;</button>
                </div>
                <div class="flex-1 relative">
                    <div ref="mapRef" class="w-full h-full bg-gray-100"></div>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none pb-12">
                        <div class="text-6xl drop-shadow-2xl">📍</div>
                    </div>
                </div>
                <div class="p-8 bg-white border-t flex gap-4">
                    <button @click="closeModal" class="px-8 py-4 bg-gray-100 text-gray-500 rounded-2xl font-black">キャンセル</button>
                    <button @click="confirmMapSelection" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black text-xl shadow-lg">ここに決定</button>
                </div>
            </div>
        </div>

        <!-- 通知 -->
        <Transition name="slide">
            <div v-if="toast.show" class="fixed top-10 right-10 z-[200] px-8 py-4 rounded-full shadow-2xl border-2 bg-white font-black" :class="toast.type === 'success' ? 'border-green-100 text-green-600' : 'border-red-100 text-red-600'">
                {{ toast.message }}
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.3s ease-out; }
.slide-enter-from { transform: translateX(100px); opacity: 0; }
.slide-leave-to { opacity: 0; }
</style>