<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    role: String,
});
</script>

<template>
    <Head title="Dispatch Request" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">配車依頼フォーム</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4 text-xl font-medium text-gray-700">配車を依頼する場所と目的地を入力してください。</p>
                        <p class="text-gray-600">あなたの権限: <span class="font-bold uppercase text-gray-900">{{ role }}</span></p>

                        <form class="mt-8 space-y-4">
                            <div>
                                <label for="start_location" class="block text-sm font-medium text-gray-700">出発地</label>
                                <input type="text" id="start_location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="例: 現在地、自宅、北九州市役所">
                            </div>

                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700">目的地</label>
                                <input type="text" id="destination" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="例: 小倉駅、病院、取引先">
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-150 transform hover:scale-[1.01]">
                                配車をリクエスト
                            </button>
                        </form>
                        
                        <div class="mt-8 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
                            <p class="text-sm font-medium text-indigo-800">現在の依頼ステータス:</p>
                            <p class="text-lg text-indigo-700 font-bold mt-1">リクエスト待機中</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>