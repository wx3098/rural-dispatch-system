<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeRequest: {
        type: Object,
        default: null,
    },
    role: {
        type: [Object, String],
        default: null,
    } 
});

const form = useForm({
    start_location: '',
    end_location: '',
    requested_pickup_datetime: '',
});

const submit = () => {
    // route()関数のエラーを徹底回避
    let url = '/user/dispatch';
    try {
        if (typeof route !== 'undefined') {
            url = route('user.dispatch.store');
        }
    } catch (e) {
        console.error('Route error:', e);
    }
    
    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

// 計算プロパティの中身もすべて「?」で保護
const isFormLocked = computed(() => {
    return !!(props.activeRequest && props.activeRequest?.status !== 'completed');
});

const isDriverAssigned = computed(() => {
    return !!(props.activeRequest?.driver_id);
});

const currentStatusText = computed(() => {
    if (!props.activeRequest) return '現在リクエストはありません';
    
    const status = props.activeRequest?.status;
    const hasDriver = !!props.activeRequest?.driver_id;

    if (!hasDriver) return 'ドライバーを探しています...';
    
    switch (status) {
        case 'accepted': return 'ドライバーが確定しました';
        case 'in_transit': return '配送中です';
        default: return 'ドライバーが向かっています';
    }
});

// roleを表示用に文字列化
const roleDisplayName = computed(() => {
    if (!props.role) return 'ユーザー';
    return typeof props.role === 'object' ? (props.role.name || 'ユーザー') : props.role;
});

// フラッシュメッセージ
const successMessage = computed(() => usePage().props.flash?.success);
const serverErrors = computed(() => usePage().props.errors || {});

</script>

<template>
    <Head title="配車依頼" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">配車依頼フォーム</h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen text-gray-900">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                
                <!-- エラーがあれば表示 -->
                <div v-if="Object.keys(serverErrors).length > 0" class="mb-4 p-4 bg-red-50 border-red-500 text-red-700 border rounded">
                    <ul class="list-disc ml-5">
                        <li v-for="(error, key) in serverErrors" :key="key">{{ error }}</li>
                    </ul>
                </div>

                <div class="bg-white shadow-xl rounded-2xl p-6 md:p-8">
                    
                    <div v-if="successMessage" class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
                        {{ successMessage }}
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xl font-bold">配車を依頼する</h3>
                        <p class="text-xs text-gray-400 mt-1 uppercase">権限: {{ roleDisplayName }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1">出発地</label>
                            <input 
                                v-model="form.start_location" 
                                :disabled="isFormLocked || form.processing"
                                type="text" 
                                class="w-full rounded-xl border-gray-300 disabled:bg-gray-100"
                                placeholder="例：北九州市立大学"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1">目的地</label>
                            <input 
                                v-model="form.end_location"
                                :disabled="isFormLocked || form.processing"
                                type="text" 
                                class="w-full rounded-xl border-gray-300 disabled:bg-gray-100"
                                placeholder="例：小倉駅"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-1">希望日時</label>
                            <input 
                                v-model="form.requested_pickup_datetime"
                                :disabled="isFormLocked || form.processing"
                                type="datetime-local" 
                                class="w-full rounded-xl border-gray-300 disabled:bg-gray-100"
                            />
                        </div>

                        <div class="pt-4">
                            <button 
                                type="submit" 
                                :disabled="isFormLocked || form.processing"
                                class="w-full py-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 disabled:bg-gray-300"
                            >
                                <span v-if="isFormLocked">リクエスト済みです</span>
                                <span v-else-if="form.processing">送信中...</span>
                                <span v-else>配車を依頼する</span>
                            </button>
                        </div>
                    </form>

                    <!-- ステータス表示 -->
                    <div v-if="props.activeRequest" class="mt-10 pt-8 border-t border-gray-200">
                        <div :class="isDriverAssigned ? 'bg-indigo-50 border-indigo-100' : 'bg-amber-50 border-amber-100'" 
                             class="p-6 rounded-2xl border-2">
                            
                            <p class="font-bold text-lg mb-4" :class="isDriverAssigned ? 'text-indigo-900' : 'text-amber-900'">
                                {{ currentStatusText }}
                            </p>

                            <div class="space-y-3 text-sm text-gray-700">
                                <p><span class="font-bold text-gray-400">ドライバー:</span> {{ props.activeRequest?.driver?.name ?? 'マッチング中' }}</p>
                                <p><span class="font-bold text-gray-400">区間:</span> {{ props.activeRequest?.start_location }} → {{ props.activeRequest?.end_location }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>