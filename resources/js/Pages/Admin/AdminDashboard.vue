<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    role: String,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">管理者ダッシュボード</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4 text-xl font-medium text-indigo-700">管理者としてログインしました。</p>
                        <p class="text-gray-600">あなたの権限: <span class="font-bold uppercase text-indigo-900">{{ role }}</span></p>

                        <div class="mt-8 border-t pt-6">
                            <h3 class="text-2xl font-semibold mb-4 text-gray-800">主要管理タスク</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                <!--1: 未処理リクエスト　-->
                                <div class="bg-red-50 p-5 rounded-lg border-l-4 border-red-500 shadow-md transition transform hover:scale-[1.02]">
                                    <p class="text-sm text-gray-500">未処理の配車リクエスト</p>
                                    <p class="text-3xl font-bold text-red-700 mt-1">15</p>
                                    <p class="text-xs text-red-500 mt-2">棋院級度が高いものを優先的に処理してください。</p>
                                </div>

                                <!--2: 稼働中ドライバー　-->
                                <div class="bg-green-50 p-5 rounded-lg border-l-4 border-green-500 shadow-md transition transform hover:scale-[1.02]">
                                    <p class="text-sm text-gray-500">稼働中のドライバー数</p>
                                    <p class="text-3xl font-bold text-green-700 mt-1">14/20</p>
                                    <p class="text-xs text-green-500 mt-2">残りのドライバーに連絡可能です。</p>
                                </div>

                                <!--3: 今日の完了タスク　-->
                                <div class="g-blue-50 p-5 rounded-lg border-l-4 border-blue-500 shadow-md transition transform hover:scale-[1.02]">
                                    <p class="sm text-gray-500">本日完了した配車</p>
                                    <p class="text-3xl font-bold text-blue-700 mt-1">50</p>
                                    <p class="text-xs text-blue-500 mt-2">過去７日間の平均: 46</p>
                                </div>
                            </div>
                        </div>

                        <!-- 簡易マップと一覧表のプレースホルダー　-->
                        <div class="mt-8">
                            <h3 class="text-2xl font-semibold mb-4 text-gray-800">ライブ状況</h3>
                            <div class="bg-gray-100 h-64 p-4 rounded-lg flex items-center justify-center text-gray-500 border border-dashed">
                                ドライバーとリクエストのリアルタイムマップと一覧表のプレースホルダー
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
