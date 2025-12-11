<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { computed } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const page = usePage();
//新規追加ロジック: ロールに基づいてダッシュボードルートを動的に決定
const userRole = computed(() => page.props.auth.user?.role); 
const isAuthenticated = computed(() => !!page.props.auth.user);

const dashboardRoute = computed(() => {
    if (!isAuthenticated.value) return 'login'; 

    switch (userRole.value) {
        case 'admin':
            return 'admin.dashboard';
        case 'driver':
            return 'driver.dashboard';
        case 'user':
            // ユーザーは配車依頼フォームがメインルート
            return 'user.dispatch.form'; 
        default:
            return '/';
    }
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
     <Head title="Welcome" />

    <div
        class="relative min-h-screen bg-gray-100 bg-center selection:bg-red-500 selection:text-white sm:flex sm:items-center sm:justify-center dark:bg-gray-900 dark:bg-dots-lighter dark:selection:bg-red-600"
    >
        <div v-if="canLogin" class="p-6 text-right sm:fixed sm:right-0 sm:top-0">
            <Link
                v-if="$page.props.auth.user"
                :href="route(dashboardRoute)"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-1 focus-visible:ring-white dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
                {{ userRole === 'user' ? '配車依頼' : 'ダッシュボード' }}
            </Link>

            <template v-else>
                <Link
                    :href="route('login')"
                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-1 focus-visible:ring-white dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                >
                    Log in
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-1 focus-visible:ring-white dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                >
                    Register
                </Link>
            </template>
        </div>

        <div class="mx-auto max-w-7xl p-6 lg:p-8">
            <div class="flex justify-center">
                <!-- Application Logo -->
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
            </div>

            <div class="mt-16 text-center">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                    配車管理システムへようこそ
                </h1>
                <p class="mt-4 text-gray-600 dark:text-gray-400">
                    お客様はログイン済みです。右上またはブラウザのナビゲーションからダッシュボードへアクセスしてください。
                </p>
                
                <div class="mt-6">
                    <Link
                        v-if="isAuthenticated"
                        :href="route(dashboardRoute)"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out"
                    >
                        {{ userRole === 'user' ? '配車依頼フォームへ' : 'ダッシュボードへ' }}
                    </Link>
                </div>
            </div>

            <!-- Footer Section (Laravel/PHP Version Info) - 省略 -->
            <div class="flex justify-center mt-16 px-0 sm:items-center sm:justify-between">
                <div class="text-center text-sm text-gray-500 sm:text-left">
                    <div class="flex items-center gap-4">
                        <a
                            href="https://laravel.com/docs"
                            class="group inline-flex items-center hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:hover:text-white dark:focus:ring-offset-gray-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                class="-mt-px mr-1 w-5 stroke-gray-400 group-hover:stroke-gray-600 dark:stroke-gray-600 dark:group-hover:stroke-gray-400"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0113.91 3.75l-4.72 4.72a4.99 4.99 0 00-1.416 3.536c0 1.258.497 2.473 1.416 3.392l3.414 3.414a1.734 1.734 0 001.229.51c.45 0 .89-.176 1.229-.51l1.414-1.414a3.52 3.52 0 000-4.977 3.52 3.52 0 00-4.977 0l-1.414 1.414a.877.877 0 01-1.237 0 .877.877 0 010-1.237l1.414-1.414a5.27 5.27 0 017.464 0 5.27 5.27 0 010 7.464l-3.414 3.414a6.837 6.837 0 01-4.72 1.96c-1.895 0-3.73-.728-5.15-2.062a.877.877 0 011.237-1.237c1.077.96 2.482 1.492 3.896 1.492a4.332 4.332 0 003.063-1.272l3.414-3.414a1.92 1.92 0 000-2.716 1.92 1.92 0 00-2.716 0l-4 4a.877.877 0 01-1.237 0z"
                                />
                            </svg>
                            Laravel Docs
                        </a>
                        <a
                            href="https://laracasts.com"
                            class="group inline-flex items-center hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:hover:text-white dark:focus:ring-offset-gray-800"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                class="mr-1 w-5 stroke-gray-400 group-hover:stroke-gray-600 dark:stroke-gray-600 dark:group-hover:stroke-gray-400"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.9 8.25c.196.2.294.45.294.75a.998.998 0 01-.294.75l-4.59 4.59a.998.998 0 01-.75.294.998.998 0 01-.75-.294L7.875 12"
                                />
                            </svg>
                            Laracasts
                        </a>
                    </div>
                </div>

                <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </div>
            </div>
        </div>
    </div>
</template>