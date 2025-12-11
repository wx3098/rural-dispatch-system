<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';

    const props = defineProps({
        role: String,
    });
</script>

<template>
    <Head title="Driver Dashboard" />

    <AuthenticatedLayout>
        <template #header>
           <h2 class="font-semibold text-xl text-gray-800 leading-tight">ドライバーダッシュボード</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4 text-xl font-medium text-blue-700">ドライバーとしてログインしました。</p>
                        <p class="text-gray-600">あなたの権限: <span class="font-bold uppercase text-blue-900">{{ role }}</span></p>
                   
                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-2xl font-semibold mb-4 text-gray-800">現在のタスク</h3>
                            
                            <div class="bg-yellow-50 p-5 rounded-lg border-l-4 border-yellow-500 shadow-md">
                                <p class="text-sm text-gray-700 font-bold">配車ID: #10023</p>
                                <p class="text-xl font-semibold text-yellow-800 mt-1">次の目的地: 福岡県北九州市 小倉駅前</p>
                                <p class="text-sm text-yellow-600 mt-2">ステータス: 走行中 (推定到着時間 15:30)</p>
                            </div>
                        </div>

                        <!-- ステータス更新ボタンエリア -->
                        <div class="mt-6 flex space-x-4">
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-150">
                                タスク完了
                            </button>
                            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg shadow transition duration-150">
                                休憩開始
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>