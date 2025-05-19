<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from 'vue-qrcode-reader'
import Swal from 'sweetalert2'
import axios from 'axios';
/* const personData = ref({
    name: 'Juan Pérez',
    plan: 'Plan Premium',
    isDelinquent: false,
}); */

const personData = ref({});
const isLoading = ref(false);
const paused = ref(false);
const isMobile = ref(false);

/*** select camera ***/

const selectedDevice = ref(null)
const devices = ref([])

onMounted(async () => {
    devices.value = (await navigator.mediaDevices.enumerateDevices()).filter(
        ({ kind }) => kind === 'videoinput'
    )

    if (devices.value.length > 0) {
        selectedDevice.value = devices.value[0]
    }

    console.log(devices.value)

    // Check if mobile on mount
    checkMobile();
    // Add resize listener
    window.addEventListener('resize', checkMobile);

    // Keep the input focused
    focusInput();
})

// Clean up event listener
onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
})

  const trackFunctionOptions = [
    { text: 'nothing (default)', value: undefined },
    { text: 'outline', value: paintOutline },
    { text: 'centered text', value: paintCenterText },
    { text: 'bounding box', value: paintBoundingBox }
]
const trackFunctionSelected = ref(trackFunctionOptions[1])


/*** error handling ***/

const error = ref('')

function onError(err) {
    error.value = `[${err.name}]: `

    const triedFrontCamera = facingMode.value === 'user';
    const triedRearCamera = facingMode.value === 'environment';

    const cameraMissingError = err.value === 'OverconstrainedError'


    if (triedFrontCamera && !cameraMissingError) {
        noRearCamera.value = true
    }

    if (triedRearCamera && !cameraMissingError) {
        noFrontCamera.value = true
    }

    //console.error(error)

  if (err.name === 'NotAllowedError') {
    error.value += 'you need to grant camera access permission'
  } else if (err.name === 'NotFoundError') {
    error.value += 'no camera on this device'
  } else if (err.name === 'NotSupportedError') {
    error.value += 'secure context required (HTTPS, localhost)'
  } else if (err.name === 'NotReadableError') {
    error.value += 'is the camera already in use?'
  } else if (err.name === 'OverconstrainedError') {
    error.value += 'installed cameras are not suitable'
  } else if (err.name === 'StreamApiNotSupportedError') {
    error.value += 'Stream API is not supported in this browser'
  } else if (err.name === 'InsecureContextError') {
    error.value +=
      'Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.'
  } else {
    error.value += err.message
  }
}

/*** track functons ***/

function paintOutline(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const [firstPoint, ...otherPoints] = detectedCode.cornerPoints

    ctx.strokeStyle = 'red'

    ctx.beginPath()
    ctx.moveTo(firstPoint.x, firstPoint.y)
    for (const { x, y } of otherPoints) {
      ctx.lineTo(x, y)
    }
    ctx.lineTo(firstPoint.x, firstPoint.y)
    ctx.closePath()
    ctx.stroke()
  }
}
function paintBoundingBox(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const {
      boundingBox: { x, y, width, height }
    } = detectedCode

    ctx.lineWidth = 2
    ctx.strokeStyle = '#007bff'
    ctx.strokeRect(x, y, width, height)
  }
}
function paintCenterText(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const { boundingBox, rawValue } = detectedCode

    const centerX = boundingBox.x + boundingBox.width / 2
    const centerY = boundingBox.y + boundingBox.height / 2

    const fontSize = Math.max(12, (50 * boundingBox.width) / ctx.canvas.width)

    ctx.font = `bold ${fontSize}px sans-serif`
    ctx.textAlign = 'center'

    ctx.lineWidth = 3
    ctx.strokeStyle = '#35495e'
    ctx.strokeText(detectedCode.rawValue, centerX, centerY)

    ctx.fillStyle = '#5cb984'
    ctx.fillText(rawValue, centerX, centerY)
  }
}

const onDetect = async (result) => {
    const code = result[0].rawValue
    console.log(code)
    try {
        isLoading.value = true;
        error.value = '';
        const response = await axios.post(route('attendance.store'), { code });
        personData.value.name = response.data.user.name;
        personData.value.plan_name = response.data.user.plan_name;
        personData.value.is_moroso = response.data.user.is_moroso;
        personData.value.id_number = response.data.user.id_number;
        personData.value.attendance_count = response.data.user.attendance_count;

        if (response.data.error) {
            error.value = response.data.message;
            //isLoading.value = false;
            Swal.fire({
                title: 'Error',
                text: response.data.message,
                icon: 'error',
            });
        }

        paused.value = true;
        await new Promise(resolve => setTimeout(resolve, 500));
        paused.value = false;

    } catch (err) {
        console.error('Error al registrar asistencia:', err);
        error.value = 'Usuario no encontrado o error en el registro';
        isLoading.value = false;
    }
}

// Add new function for form submission
const handleSubmit = async (event) => {
    event.preventDefault();
    const code = event.target.code.value;
    if (!code) return;

    try {
        isLoading.value = true;
        error.value = '';
        const response = await axios.post(route('attendance.store'), { code });
        personData.value.name = response.data.user.name;
        personData.value.plan_name = response.data.user.plan_name;
        personData.value.is_moroso = response.data.user.is_moroso;
        personData.value.id_number = response.data.user.id_number;
        personData.value.attendance_count = response.data.user.attendance_count;

        if (response.data.error) {
            error.value = response.data.message;
            Swal.fire({
                title: 'Error',
                text: response.data.message,
                icon: 'error',
            });
        }

        // Clear the input after successful submission
        event.target.code.value = '';
        // Keep focus on input
        event.target.code.focus();

    } catch (err) {
        console.error('Error al registrar asistencia:', err);
        error.value = 'Usuario no encontrado o error en el registro';
        isLoading.value = false;
    }
}

// Add ref for input focus
const inputRef = ref(null);

// Add function to focus input
const focusInput = () => {
    if (inputRef.value) {
        inputRef.value.focus();
    }
}

// Add function to check if device is mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" @click="focusInput">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-center">
                    <img src="/images/logosmall2.png" alt="Logo" class="h-16 w-auto" />
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- QR Scanner Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                            Escanear Código
                        </h2>

                        <!-- Error Messages -->
                        <div v-if="error" class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 rounded-lg">
                            <p class="text-red-600 dark:text-red-400">{{ error }}</p>
                        </div>

                        <!-- Form for manual input -->
                        <form @submit="handleSubmit" class="space-y-4">
                            <div class="relative">
                                <input
                                    ref="inputRef"
                                    type="text"
                                    name="code"
                                    class="w-full px-4 py-3 text-lg border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Ingrese el código..."
                                    autofocus
                                    autocomplete="off"
                                />
                            </div>
                            <button
                                type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Registrar
                            </button>
                        </form>

                        <!-- Camera section only for mobile -->
                        <div v-if="isMobile" class="mt-6">
                            <div class="relative aspect-square max-w-md mx-auto">
                                <qrcode-stream
                                    :paused="paused"
                                    :track="trackFunctionSelected.value"
                                    @error="onError"
                                    @detect="onDetect"
                                    class="rounded-lg overflow-hidden"
                                >
                                    <button @click="switchCamera" class="absolute top-4 left-4 bg-white/80 dark:bg-gray-800/80 p-2 rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-700 transition-colors">
                                        <img src="/images/camera.svg" alt="switch camera" class="w-6 h-6" />
                                    </button>
                                </qrcode-stream>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Info Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" v-if="isLoading">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            Información del Usuario
                        </h2>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Cédula:</span>
                                <span class="text-gray-900 dark:text-white">{{ personData.id_number }}</span>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Nombre:</span>
                                <span class="text-gray-900 dark:text-white">{{ personData.name }}</span>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Plan:</span>
                                <span class="text-gray-900 dark:text-white">{{ personData.plan_name }}</span>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Estado:</span>
                                <span :class="personData.is_moroso ? 'text-red-500' : 'text-green-500'">
                                    {{ personData.is_moroso ? 'Moroso' : 'Al día' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <span class="text-gray-600 dark:text-gray-300 font-medium">Asistencias:</span>
                                <span class="text-gray-900 dark:text-white">{{ personData.attendance_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
    .switchCamera {
        position: absolute;
        left: 10px;
        top: 10px;
        color : red;

    }
    .switchCamera img {
        width: 30px;
        height: 30px;
    }
    .error {
        color: red;
        font-weight: bold;
    }
</style>
