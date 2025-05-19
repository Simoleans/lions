<template>

    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="default" @click="isOpen = true" class="w-full">Renovar Plan</Button>
        </DialogTrigger>
        <DialogContent class="!max-w-[90vw] !w-[70vw] !h-[60vh] overflow-y-auto">
            <DialogHeader class="space-y-3">
                <DialogTitle>Renovar Plan</DialogTitle>
                <DialogDescription>
                Por favor, sube el documento para agregarlos a la base de datos.
                {{ form.plan_id }}
                </DialogDescription>
            </DialogHeader>

            <div class="col-span-2">
                <label for="id_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Plan</label>
            <ul class="grid w-full gap-6 md:grid-cols-2">
                <li v-for="plan in user.plans" :key="plan.id">
                    <Input
                        type="radio"
                        :id="`plan-${plan.id}`"
                        :value="plan.id"
                        v-model="form.plan_id"
                        class="hidden peer"
                        required
                        @click="selectPlan(plan.id)"
                    />
                    <Label
                        :for="`plan-${plan.id}`"
                        class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer  transition"
                        @click="selectPlan(plan.id)"
                        :class="{ 'border-purple-600': form.plan_id == plan.id }"
                    >
                        <div class="flex items-center flex-col gap-3 mb-3">
                            <img src="/images/logo.png" class="h-6 mr-3 sm:h-9" alt="Lions Logo" />
                            <div class="text-lg font-semibold">{{ plan.name }}</div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ plan.classes_per_month }} clases por mes
                        </div>
                        <div class="mt-2 font-semibold text-gray-800 dark:text-gray-200">
                            {{ formatPrice(plan.price) }}
                        </div>
                    </Label>
                </li>

                <!-- personalizada -->
                <li>
                    <Input
                        type="radio"
                        id="plan-custom"
                        value="custom"
                        v-model="form.plan_id"
                        class="hidden peer"
                        required
                        @click="selectPlan('custom')"
                    />
                    <Label
                        for="plan-custom"
                        class="inline-flex flex-col items-start justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer transition"
                        @click="selectPlan('custom')"
                        :class="{ 'border-purple-600': form.plan_id == 'custom' }"
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
                        </div>
                    </Label>
                </li>
            </ul>
            <DialogFooter class="flex justify-start gap-2 mt-4" v-if="form.plan_id">
                <Button variant="outline" @click="isOpen = false">Cerrar</Button>
                <Button variant="default" @click="handleRenew">Renovar</Button>
            </DialogFooter>
        </div>

        </DialogContent>

    </Dialog>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { Dialog, DialogTrigger, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ref, watch } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const { notify } = useNotifications()
const props = defineProps({
    user: Object,
});

const isOpen = ref(false)
const page = usePage()
const form = useForm({
    plan_id: null,
    remaining_classes: null,
});

const formatPrice = (price) => {
    const usdCurrency = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    return usdCurrency.format(price);
}

const handleRenew = () => {
    if (!form.plan_id) {
        notify({ message: 'Debes seleccionar un plan', type: 'error' })
        return;
    }
    form.post(route('users.renew-subscription', { user: props.user.id }), {
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
            //notify({ message: 'Plan renovado correctamente', type: 'success' })
        }
    });
}

const selectPlan = (planId) => {
    form.plan_id = planId;
    if (planId === 'custom') {
        form.remaining_classes = 1;
    } else {
        form.remaining_classes = null;
    }
}

watch(isOpen, (newVal) => {
    if (!newVal) {
        form.reset()
    }
})
</script>
