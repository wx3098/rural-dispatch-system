<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    // コントローラーから渡される現在アクティブなリクエスト情報
    activeRequest: {
        type: Object,
        default: null,
    },
    role: String, 
});

// Inertia Formの定義
const form = useForm({
    start_location: '',
    end_location: '',
    requested_pickup_datetime: '', // ★ 必須項目: 希望乗車日時 ★
});

// フォームの送信処理
const submit = () => {
    // フォーム送信は user.dispatch.store ルートへPOSTします
    form.post(route('user.dispatch.store'), {
        onSuccess: () => {
            // 成功したらフォームをリセット
            form.reset('start_location', 'end_location', 'requested_pickup_datetime');
        },
    });
};

// アクティブなリクエストが存在する場合、フォームをロック（操作不可）にする
const isFormLocked = computed(() => !!props.activeRequest);

// フラッシュメッセージ (成功/エラー) を取得
const successMessage = computed(() => usePage().props.flash?.success);
const errors = computed(() => usePage().props.errors);

// 画面に表示する現在のステータスを計算
const currentStatusText = computed(() => {
    if (props.activeRequest) {
        switch (props.activeRequest.status) {
            case 'pending':
                return 'リクエスト待機中 (処理待ち)';
            case 'assigned':
                return 'ドライバー割り当て済み';
            default:
                return 'ステータス不明';
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
                    
                    <!-- 成功メッセージ表示 -->
                    <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ successMessage }}
                    </div>
                    <!-- エラーメッセージ表示 (リクエスト制限エラーなど) -->
                    <div v-if="errors.request_limit" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ errors.request_limit }}
                    </div>

                    <h3 class="text-2xl font-bold mb-4 text-gray-700">配車を依頼する場所と目的地を入力してください</h3>
                    <p class="text-sm text-gray-500 mb-6">あなたの権限: <span class="font-medium text-indigo-600">{{ role || 'ユーザー' }}</span></p>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- 出発地 -->
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

                        <!-- 目的地 -->
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
                        
                        <!-- 希望乗車日時 -->
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


                        <!-- リクエストボタン -->
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

                    <!-- 現在のリクエストステータス -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">現在のリクエストステータス</h4>
                        <div 
                            :class="[
                                'p-4 rounded-lg font-medium',
                                isFormLocked ? 'bg-yellow-100 text-yellow-800 border border-yellow-400' : 'bg-green-100 text-green-800 border border-green-400'
                            ]"
                        >
                            {{ currentStatusText }}
                            <div v-if="isFormLocked && activeRequest" class="text-sm mt-1 text-yellow-600">
                                <span v-if="activeRequest.status === 'pending'">管理者がドライバーを割り当てるのをお待ちください。</span>
                                <span v-else>ドライバーが向かっています。</span>
                                <p class="text-xs mt-1">出発地: {{ activeRequest.start_location }} / 目的地: {{ activeRequest.end_location }}</p>
                                <p class="text-xs mt-1">希望日時: {{ new Date(activeRequest.requested_pickup_datetime).toLocaleString('ja-JP') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>