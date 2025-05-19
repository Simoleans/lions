<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed } from 'vue';

const props = defineProps({
    plans: Array,
    user: Object,
});

const form = useForm({
    id_number: props.user.id_number || '',
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
    allergy: props.user.allergy || '',
    injury: props.user.injury || '',
    plan_id: props.user.subscriptions.plan_id || '',
    remaining_classes: props.user.subscriptions.remaining_classes || '',
});

const formatPrice = (price) => {
    const usdCurrency = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    return usdCurrency.format(price);
};

const isFormValid = computed(() => {
    return (
        form.name.trim() !== '' &&
        form.email.trim() !== '' &&
        form.phone.trim() !== '' &&
        form.plan_id !== ''
    );
});

const handleEdit = () => {
    if (form.plan_id !== 'custom') {
        form.remaining_classes = null;
    }

    form.put(route('users.update', props.user.id), {
        onSuccess: () => {
            form.defaults(form); // Actualiza los valores por defecto después de guardar
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Editar usuario
            </h2>
        </header>

        <form @submit.prevent="handleEdit" class="mt-6 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <Label for="id_number">Cédula</Label>
                    <Input id="id_number" type="text" class="mt-1 block w-full" v-model="form.id_number" autofocus />
                    <InputError class="mt-2" :message="form.errors.id_number" />
                </div>

                <div>
                    <Label for="name">Nombre</Label>
                    <Input id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <Label for="phone">Teléfono</Label>
                    <Input id="phone" type="text" class="mt-1 block w-full" v-model="form.phone" required />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <Label for="allergy">Alergia</Label>
                    <select id="allergy" class="mt-1 block w-full border border-gray-300 p-[7px] rounded-md" v-model="form.allergy">
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
                    <Input id="injury" type="text" class="mt-1 block w-full" v-model="form.injury" />
                    <InputError class="mt-2" :message="form.errors.injury" />
                </div>

                <div class="col-span-2">
                    <Label>Plan</Label>
                    <ul class="grid w-full gap-6 md:grid-cols-3">
                        <li v-for="plan in plans" :key="plan.id">
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
                                class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 hover:text-gray-600 hover:bg-gray-50 transition"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <img src="/images/logo.png" class="h-6 mr-3 sm:h-9" alt="Lions Logo" />
                                    <div class="text-lg font-semibold">{{ plan.name }}</div>
                                </div>
                                <div class="text-sm text-gray-600">{{ plan.classes_per_month }} clases por mes</div>
                                <div class="mt-2 font-semibold text-gray-800">
                                    {{ formatPrice(plan.price) }}
                                </div>
                            </label>
                        </li>

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
                                class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 hover:text-gray-600 hover:bg-gray-50 transition"
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

                    <InputError class="mt-2" :message="form.errors.plan_id" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <Button class="cursor-pointer" variant="default" :disabled="form.processing || !isFormValid">Actualizar</Button>
                <Link :href="route('dashboard')" class="text-sm text-gray-600 dark:text-gray-400">Cancelar</Link>
            </div>
        </form>
    </section>
</template>
