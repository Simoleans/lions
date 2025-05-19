<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import FormEdit from './Components/FormEdit.vue';

const breadcrumbs = [
    { title: 'Usuarios', href: '/users' },
    { title: 'Editar Usuario', href: '/users/edit' },
];

defineProps({
    plans: Array,
    user: Object,
});
</script>

<template>
    <Head title="Registrar Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Editar Usuario
            </h2>
        </template>

        <div class="py-12 px-3">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800"
                >
                    <FormEdit :plans="plans" :user="user" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
