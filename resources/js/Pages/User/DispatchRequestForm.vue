<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    activeRequest: {
        type: Object,
        default: null,
    },
    role: String, 
});

const form = useForm({
    start_location: '',
    end_location: '',
    requested_pickup_datetime: '',
});

const submit = () => {
    form.post(route('user.dispatch.store'), {
        onSuccess: () => {
            form.reset('start_location', 'end_location', 'requested_pickup_datetime');
        },
    });
};

// フォームをロックする条件：リクエストが存在し、かつ完了（completed）していない場合
const isFormLocked = computed(() => !!props.activeRequest);

// ★ 修正ポイント：ドライバーが確定したかを厳密に判定する
// driver_id が入っている、かつステータスが pending ではない場合
const isDriverAssigned = computed(() => {
    return props.activeRequest && props.activeRequest.driver_id !== null;
});

const successMessage = computed(() => usePage().props.flash?.success);
const errors = computed(() => usePage().props.errors);

const currentStatusText = computed(() => {
    if (props.activeRequest) {
        // ステータスと driver_id の両方を見てテキストを出し分ける
        if (!props.activeRequest.driver_id) {
            return 'ドライバーを探しています... (待機中)';
        }
        
        switch (props.activeRequest.status) {
            case 'accepted':
                return 'ドライバーが確定しました';
            case 'in_transit':
                return '現在、配送中です';
            default:
                return 'ドライバーが向かっています';
        }
    }
    return '現在アクティブなリクエストはありません。';
});
</script>

<template>
    <Head title="配車依頼" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">配車依頼フォーム</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                    
                    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ successMessage }}
                    </div>
                    <div v-if="errors.request_limit" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ errors.request_limit }}
                    </div>

                    <h3 class="text-2xl font-bold mb-4 text-gray-700">配車を依頼する場所と目的地を入力してください</h3>
                    <p class="text-sm text-gray-500 mb-6">あなたの権限: <span class="font-medium text-indigo-600">{{ role || 'ユーザー' }}</span></p>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="start_location" class="block text-sm font-medium text-gray-700">出発地</label>
                            <input 
                                id="start_location" 
                                type="text" 
                                v-model="form.start_location" 
                                :disabled="isFormLocked || form.processing"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-500"
                                placeholder="例: 現在地、自宅、北九州市役所"
                            />
                            <div v-if="form.errors.start_location" class="text-sm text-red-600 mt-1">{{ form.errors.start_location }}</div>
                        </div>

                        <div>
                            <label for="end_location" class="block text-sm font-medium text-gray-700">目的地</label>
                            <input 
                                id="end_location" 
                                type="text" 
                                v-model="form.end_location"
                                :disabled="isFormLocked || form.processing"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-500"
                                placeholder="例: 小倉駅、病院、取引先"
                            />
                            <div v-if="form.errors.end_location" class="text-sm text-red-600 mt-1">{{ form.errors.end_location }}</div>
                        </div>
                        
                        <div>
                            <label for="requested_pickup_datetime" class="block text-sm font-medium text-gray-700">希望乗車日時</label>
                            <input 
                                id="requested_pickup_datetime" 
                                type="datetime-local" 
                                v-model="form.requested_pickup_datetime"
                                :disabled="isFormLocked || form.processing"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-50 disabled:text-gray-500"
                            />
                            <p class="text-xs text-gray-500 mt-1">現在時刻よりも後の日時を指定してください。</p>
                            <div v-if="form.errors.requested_pickup_datetime" class="text-sm text-red-600 mt-1">{{ form.errors.requested_pickup_datetime }}</div>
                        </div>

                        <div class="pt-4">
                            <button 
                                type="submit" 
                                :disabled="isFormLocked || form.processing"
                                class="w-full justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-lg font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:bg-indigo-300 disabled:cursor-not-allowed"
                            >
                                <span v-if="isFormLocked">リクエストは処理中です...</span>
                                <span v-else-if="form.processing">リクエスト送信中...</span>
                                <span v-else>配車をリクエスト</span>
                            </button>
                        </div>
                    </form>

                    <!-- 現在のリクエストステータス表示エリア -->
                    <div v-if="activeRequest" class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">現在の状況</h4>
                        
                        <!-- ドライバー未確定 (driver_id が null) -->
                        <div v-if="!isDriverAssigned" class="p-4 rounded-lg bg-yellow-50 border border-yellow-200">
                            <div class="flex items-center">
                                <span class="animate-pulse mr-2 text-yellow-500">●</span>
                                <p class="text-yellow-800 font-bold">{{ currentStatusText }}</p>
                            </div>
                            <p class="text-sm mt-2 text-yellow-700">近隣のドライバーにリクエストを送信しています。確定までしばらくお待ちください。</p>
                        </div>

                        <!-- ドライバー確定済み (driver_id が存在) -->
                        <div v-else class="p-4 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-800">
                            <div class="flex justify-between items-center mb-2">
                                <p class="font-bold text-lg">{{ currentStatusText }}</p>
                                <span class="bg-indigo-600 text-white text-xs px-2 py-1 rounded">確定</span>
                            </div>
                            <div class="space-y-1 text-sm">
                                <p>担当ドライバー: <span class="font-bold">{{ activeRequest.driver?.name || '手配中' }}</span></p>
                                <p>出発地: {{ activeRequest.start_location }}</p>
                                <p>到着地: {{ activeRequest.end_location }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>