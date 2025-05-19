<script setup>
//import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, router,Link } from '@inertiajs/vue3';
import { ref, watch, reactive } from 'vue';
import debounce from 'lodash.debounce';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { EyeIcon, PlusIcon, PencilIcon } from 'lucide-vue-next';
import { BarChart } from 'vue-chart-3';
/* import ShowUser from './Components/ShowUser.vue';
import EditUser from './Components/EditUser.vue'; */

const props = defineProps({
    attendances: Object,
    filters: Object,
    stats: Object,
});

const searchTerm = ref(props.filters.search || '');
const dateFilter = ref(props.filters.date || '');

const open = ref(false);
const user = ref(null);
const editUserData = ref({});
const modalEditUser = ref(false);

// Configuración del gráfico
const chartDataBar = reactive({
    labels: props.stats.lastFiveDays.map(day => day.label),
    datasets: [
        {
            label: 'Asistencias',
            data: props.stats.lastFiveDays.map(day => day.count),
            backgroundColor: '#4CAF50',
            borderColor: '#4CAF50',
            borderWidth: 1
        },
    ],
});

const chartOptionsBar = reactive({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top'
        },
        title: {
            display: true,
            text: 'Asistencias de los últimos 5 días'
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
                precision: 0
            }
        }
    }
});

// Buscador con debounce para no saturar el servidor
const searchAttendances = debounce((value) => {
    router.get(
        route('attendance.index'),
        {
            search: value,
            date: dateFilter.value
        },
        { preserveState: true, replace: true }
    );
}, 400);

// Watch para la búsqueda
watch(searchTerm, (value) => {
    searchAttendances(value);
});

// Watch para el filtro de fecha
watch(dateFilter, (value) => {
    router.get(
        route('attendance.index'),
        {
            search: searchTerm.value,
            date: value
        },
        { preserveState: true, replace: true }
    );
});

// Cambiar página (preserva los filtros)
const changePage = (url) => {
    if (url) {
        router.get(url, {
            search: searchTerm.value,
            date: dateFilter.value
        }, { preserveState: true, preserveScroll: true });
    }
};

const viewUser = (data) => {
    console.log(data);
    user.value = data;
    open.value = true;

}

const closeModal = () => {
    open.value = false;
}

const openEditUser = (user) => {
    editUserData.value = user;
    modalEditUser.value = true;
}

const closeEditUser = () => {
    modalEditUser.value = false;
}
</script>

<template>
    <Head title="Asistencias" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Registro de Asistencias
            </h2>
        </template>

        <div class="py-12 px-3">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Gráfico -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800 grid grid-cols-2 md:grid-cols-2 gap-4">
                    <BarChart :chart-data="chartDataBar" :chart-options="chartOptionsBar" />
                    <!-- card total de asistencias -->
                    <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Estadísticas de Asistencias</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300">Hoy:</span>
                                <span class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ stats.attendanceStats.today }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300">Esta Semana:</span>
                                <span class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ stats.attendanceStats.week }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300">Este Mes:</span>
                                <span class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ stats.attendanceStats.month }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla y Filtros -->
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                    <!-- Filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Buscador -->
                        <div>
                            <Input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Buscar por nombre o cédula..."
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>
                        <!-- Filtro de fecha -->
                        <div>
                            <Input
                                v-model="dateFilter"
                                type="date"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Cédula</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Hora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="attendance in attendances.data" :key="attendance.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ attendance.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ attendance.user.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ attendance.user.id_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ attendance.subscription?.plan?.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ new Date(attendance.attended_at).toLocaleDateString() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">{{ new Date(attendance.attended_at).toLocaleTimeString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="flex justify-between items-center mt-4">
                        <button v-if="attendances.prev_page_url" @click="changePage(attendances.prev_page_url)" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded">
                            Anterior
                        </button>

                        <span class="text-gray-600 dark:text-gray-400 text-sm">
                            Página {{ attendances.current_page }} de {{ attendances.last_page }}
                        </span>

                        <button v-if="attendances.next_page_url" @click="changePage(attendances.next_page_url)" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded">
                            Siguiente
                        </button>
                    </div>

                    <div v-if="attendances.data.length === 0" class="text-center text-gray-500 dark:text-gray-400 mt-4">
                        No se encontraron registros de asistencia.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <!-- <ShowUser :user="user" :open="open" @close="closeModal" @openEdit="openEditUser" />
    <EditUser :user="editUserData" :open="modalEditUser" @close="closeEditUser" /> -->
</template>
