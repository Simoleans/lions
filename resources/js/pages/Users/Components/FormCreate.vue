<script setup>
//import InputError from '@/Components/InputError.vue';
//import InputLabel from '@/Components/InputLabel.vue';
//import PrimaryButton from '@/Components/PrimaryButton.vue';
//import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2'
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const user = usePage().props.auth.user;

const props = defineProps({
    plans: Array,
});

const form = useForm({
    id_number: '',
    name: '',
    email: '',
    role: '',
    phone: '',
    allergy: '',
    injury: '',
    plan_id: '',
    start_date: '',
    remaining_classes: '',
});

const formatPrice = (price) => {

   const usdCurrency = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
   return usdCurrency.format(price);

}

import { computed } from 'vue';

// Computed para validar el formulario
const isFormValid = computed(() => {
    return (
        form.name.trim() !== '' &&
        form.email.trim() !== '' &&
        form.phone.trim() !== '' &&
        form.role !== '' &&
        form.plan_id !== '' &&
        form.start_date !== ''
    );
});

const handleStore = () => {

    if (form.plan_id !== 'custom') {
        form.remaining_classes = null; // Limpiar si no es personalizado
    }

    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
        }
    });
}

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Registrar nuevo usuario
            </h2>
        </header>

        <form
            @submit.prevent="handleStore"
            class="mt-6 space-y-6"
        >
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label for="id_number">Cédula</Label>

                    <Input
                        id="id_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.id_number"
                        autofocus
                        autocomplete="id_number"
                    />

                    <InputError class="mt-2" :message="form.errors.id_number" />
                </div>

                <div>
                    <Label for="name">Nombre</Label>
                    <Input
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <Label for="phone">Teléfono</Label>
                    <Input
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        required
                        autocomplete="phone"
                    />

                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <Label for="allergy">Alergia</Label>
                    <!-- select option alergy-->
                    <select id="allergy" class="mt-1 block w-full border border-gray-300 p-[7px] rounded-md" v-model="form.allergy" autocomplete="allergy">
                        <option value="">Selecciona una opción</option>
                        <option value="polen">Polen</option>
                        <option value="polvo">Ácaros del polvo</option>
                        <option value="alimentos">Alimentos</option>
                        <option value="medicamentos">Medicamentos</option>
                        <option value="animales">Animales (pelo o caspa)</option>
                        <option value="insectos">Picaduras de insectos</option>
                        <option value="latex">Látex</option>
                        <option value="moho">Moho</option>
                        <option value="quimicos">Productos químicos</option>
                        <option value="otros">Otros</option>
                    </select>

                    <InputError class="mt-2" :message="form.errors.allergy" />
                </div>

                <div>
                    <Label for="injury">Lesión</Label>
                    <Input
                        id="injury"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.injury"
                        autocomplete="injury"
                    />

                    <InputError class="mt-2" :message="form.errors.injury" />
                </div>

                <div>
                    <Label for="role">Rol</Label>
                    <select id="role" class="mt-1 block w-full border border-gray-300 p-[7px] rounded-md" v-model="form.role" required autocomplete="role">
                        <option value="">Selecciona un rol</option>
                        <option value="1">Administrador</option>
                        <option value="2">Cliente</option>
                    </select>

                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div>
                    <Label for="start_date">Fecha de inicio</Label>
                    <Input
                        id="start_date"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.start_date"
                        required
                        autocomplete="start_date"
                    />

                    <InputError class="mt-2" :message="form.errors.start_date" />
                </div>

                <div class="col-span-2">
                    <Label>Plan</Label>

                    <ul class="grid w-full gap-6 md:grid-cols-3">
                        <li v-for="plan in plans" :key="plan.value">
                            <input
                                type="radio"
                                :id="`plan-${plan.id}`"
                                :value="plan.id"
                                v-model="form.plan_id"
                                class="hidden peer"
                                required
                            />
                            <label
                                :for="`plan-${plan.id}`"
                                class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 dark:peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <img src="/images/logo.png" class="h-6 mr-3 sm:h-9" alt="Lions Logo" />
                                    <div class="text-lg font-semibold">{{ plan.name }}</div>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ plan.classes_per_month }} clases por mes
                                </div>
                                <div class="mt-2 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ formatPrice(plan.price) }}
                                </div>
                            </label>
                        </li>

                        <!-- Opción personalizada -->
                        <li>
                            <input
                                type="radio"
                                id="plan-custom"
                                value="custom"
                                v-model="form.plan_id"
                                class="hidden peer"
                                required
                            />
                            <label
                                for="plan-custom"
                                class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer dark:hover:text-gray-300 dark:border-gray-700 peer-checked:border-blue-600 dark:peer-checked:border-blue-600 hover:text-gray-600 dark:peer-checked:text-gray-300 peer-checked:text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 transition"
                            >
                                <div class="text-lg font-semibold mb-3">Días Personalizados</div>

                                <div v-if="form.plan_id === 'custom'" class="w-full mt-2">
                                    <Label for="remaining_classes">Cantidad de clases</Label>
                                    <Input
                                        id="remaining_classes"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.remaining_classes"
                                        min="1"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.remaining_classes" />
                                </div>
                            </label>
                        </li>
                    </ul>


                    <InputError class="mt-2" :message="form.errors.plan" />
                </div>
            </div> <!-- fin grid -->


            <div class="flex items-center justify-end gap-4">
                <Button variant="default" :disabled="form.processing || !isFormValid">Guardar</Button>
                <Link :href="route('dashboard')" class="text-sm text-gray-600 dark:text-gray-400">Cancelar</Link>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
