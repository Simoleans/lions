<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import CalendarAttendance from './Components/CalendarAttendance.vue';
import QRCodeVue3 from 'qrcode-vue3';
import {  reactive } from 'vue';
import { BarChart, PieChart } from 'vue-chart-3';
import Swal from 'sweetalert2';
import RenewPlan from './Components/RenewPlan.vue';

const props = defineProps({
    user: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/users' },
    { title: props.user.name, href: `/users/${props.user.id}` },
];

/* const openRenewSubscription = ref(false);
const dataUser = ref({}); */

/* const renewPlan = (user: any) => {
    openRenewSubscription.value = true;
    dataUser.value = user;
}; */

function downloadQr() {
    const element = document.querySelector('.qr-dashboard-img');
    const urlItem = element?.getAttribute('src');
    if (!urlItem) return;

    axios({
        url: urlItem,
        method: 'GET',
        responseType: 'blob',
    }).then((response) => {
        const url = window.URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${props.user.id_number}.png`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    });
}

const chartDataBar = reactive({
  labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo'],
  datasets: [
    {
      label: 'Usuarios Registrados',
      data: [50, 75, 100, 125, 150],
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
      text: 'Asistencias por Mes'
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
});
</script>

<template>
    <Head :title="user.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <!-- Panel Principal: Información + QR + celandeario -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-6 flex flex-col lg:flex-row gap-8">
                    <!-- Información Personal -->
                    <div class="flex-1 space-y-4 text-[18px] text-gray-800 dark:text-white">
                        <h2 class="text-2xl font-bold text-gray-700 dark:text-white">Información del Usuario</h2>
                        <p class="flex items-center gap-3"><i class="pi pi-user text-blue-500 text-2xl"></i> <span class="font-semibold">Cédula:</span> {{ user.id_number }}</p>
                        <p class="flex items-center gap-3"><i class="pi pi-envelope text-yellow-500 text-2xl"></i> <span class="font-semibold">Correo:</span> {{ user.email }}</p>
                        <p class="flex items-center gap-3"><i class="pi pi-phone text-green-500 text-2xl"></i> <span class="font-semibold">Teléfono:</span> {{ user.phone }}</p>
                        <p class="flex items-center gap-3"><i class="pi pi-calendar text-red-500 text-2xl"></i> <span class="font-semibold">Peso Actual:</span> {{ user.weight ?? '-' }}</p>
                        <p class="flex items-center gap-3"><i class="pi pi-calendar text-red-500 text-2xl"></i> <span class="font-semibold">Alergias:</span> {{ user.allergy ? user.allergy : 'No tiene alergias' }}</p>
                        <p class="flex items-center gap-3"><i class="pi pi-shield text-purple-500 text-2xl"></i> <span class="font-semibold">Estado:</span>
                            <span :class="user.status ? 'text-green-500' : 'text-red-500'">
                                {{ user.status ? 'Activo' : 'Inactivo' }}
                            </span>
                        </p>
                    </div>

                    <!-- Código QR -->
                    <div class="flex flex-col items-center gap-4">
                        <QRCodeVue3
                            :width="180"
                            :height="180"
                            imgclass="qr-dashboard-img"
                            :value="user.id_number"
                            :qrOptions="{ typeNumber: 0, mode: 'Byte', errorCorrectionLevel: 'H' }"
                            :imageOptions="{ hideBackgroundDots: true, imageSize: 0.4, margin: 0 }"
                            :dotsOptions="{
                                type: 'square',
                                color: '#333',
                                gradient: {
                                    type: 'linear',
                                    rotation: 0,
                                    colorStops: [
                                        { offset: 0, color: '#333' },
                                        { offset: 1, color: '#333' },
                                    ],
                                },
                            }"
                            fileExt="jpeg"
                            :backgroundOptions="{ color: '#ffffff' }"
                            :cornersSquareOptions="{ type: 'dot', color: '#333' }"
                            :cornersDotOptions="{ type: undefined, color: '#333' }"
                            crossOrigin="anonymous"
                            :download="false"
                        />
                        <button @click="downloadQr" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-base rounded-md font-semibold">
                            Descargar QR
                        </button>
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-900 p-6 ">
                    <!-- <h2 class="text-2xl font-bold text-gray-700 dark:text-white mb-3">Calendario de Asistencias</h2> -->
                    <CalendarAttendance :user="user" />
                </div>


            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">

                <!-- Plan + Calendario -->
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border gap-4 bg-white dark:bg-gray-900 p-6 space-y-8">
                    <div>
                        <h2 class="text-3xl font-bold text-blue-700 dark:text-white mb-4">Plan de Suscripción</h2>
                        <div class="text-[18px] text-gray-800 dark:text-white space-y-3">
                            <p><strong>Plan:</strong> {{ user.plan_name }}</p>
                                <p v-if="user.plan_personalizado"><strong>Clases Restantes:</strong> {{ user.clases_restantes }}</p>
                            <p>
                                <strong>Estado del Plan:</strong>
                                <span :class="user.is_moroso ? 'text-red-500' : 'text-green-500'">
                                {{ user.is_moroso ? 'Expirado' : 'Activo' }}
                                </span>
                            </p>
                            <p>
                                <strong>Fecha de Expiración: </strong>
                                <span >
                                 {{ user.subscriptions.end_date }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="pt-4">
                        <!-- <button
                            @click="renewPlan(user)"
                            class="w-full px-6 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold text-lg transition duration-300"
                        >
                            Renovar Plan
                        </button> -->
                        <RenewPlan :user="user" />
                    </div>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border gap-4 bg-white dark:bg-gray-900 p-6 space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-700 dark:text-white mb-3">Avance de peso</h2>
                        <BarChart :chart-data="chartDataBar" :chart-options="chartOptionsBar" />
                    </div>
                </div>

            </div>

        </div>
    </AppLayout>
</template>
