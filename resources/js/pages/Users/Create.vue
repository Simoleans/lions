<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import FormCreate from './Components/FormCreate.vue';

const breadcrumbs = [
    { title: 'Usuarios', href: '/users' },
    { title: 'Registrar Usuario', href: '/users/create' },
];

defineProps({
    plans: Array,
});
</script>

<template>
    <Head title="Registrar Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Registrar Usuario
            </h2>
        </template>

        <div class="py-12 px-3">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                >
                    <FormCreate :plans="plans" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
