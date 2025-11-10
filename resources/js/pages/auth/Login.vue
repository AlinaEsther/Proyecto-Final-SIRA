<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const logoUrl = ref('/images/sira_logo_horizontal.png');
const backgroundImageUrl = ref('/images/background_sira.jpg');

const props = defineProps({
    localEmail: String,
    localPassword: String,
});

const page = usePage();
const appName = computed(() => page.props.name);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showLoginPassword = ref(false);

onMounted(() => {
    if (import.meta.env.MODE === 'development' || import.meta.env.MODE === 'test') {
        form.email = props.localEmail || '';
        form.password = props.localPassword || '';
    }

    const handleKeyDown = (event: KeyboardEvent) => {
        if (event.key === 'Enter') {
            submit();
        }
    };

    document.addEventListener('keydown', handleKeyDown);
    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown);
    });
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
</style>

<template>
    <div
        class="relative min-h-screen bg-cover bg-center font-sans text-gray-700"
        :style="{
            background: backgroundImageUrl ? `url(${backgroundImageUrl})` : '#ffffff',
            backgroundSize: backgroundImageUrl ? 'cover' : 'auto',
            backgroundPosition: backgroundImageUrl ? 'center' : 'initial',
            fontFamily: 'sans-serif'
        }"
    >
        <div
            class="absolute top-[50%] right-[2.5%] flex h-[450px] w-[850px] -translate-y-2/4 transform overflow-hidden rounded-xl bg-white/85 shadow-2xl"
        >
            <!-- Panel Izquierdo -->
            <div class="flex h-full w-full flex-col items-center justify-between bg-transparent px-6 py-4">
                <div class="flex w-full flex-1 flex-col items-center justify-start">
                    <img :src="logoUrl || '/favicon.ico'" alt="logo" class="mb-5 h-[130.20px] w-[395px]" />
                    <h2 class="mt-6 text-center text-lg">
                        {{ appName || 'Sistema Inteligente de Recomendaciones Académicas' }}
                    </h2>
                    <p class="text-gray-600 opacity-80">Instituto Tecnológico de Las Americas</p>
                </div>
                <div class="w-full">
                    <hr class="mb-3 w-[360px] border-gray-600" />
                    <p class="mb-[45px] text-center text-[12.3px] text-gray-500 opacity-90">
                        Desarrollado por equipo de SIRA
                        <br />
                        Todos los Derechos Reservados 2025
                    </p>
                </div>
            </div>

            <!-- Panel Derecho: Login -->
            <div class="flex h-full w-full flex-col justify-between bg-white px-6 py-4">
                <div class="flex flex-col items-center">
                    <i class="fas fa-user-circle mt-2 mb-2 text-[100px] text-gray-400"></i>
                    <h3 class="text-center text-4xl font-normal text-gray-400">Inicio de sesión</h3>
                </div>

                <form @submit.prevent="submit" class="mt-4 flex flex-1 flex-col space-y-3">
                    <!-- Campo de correo -->
                    <div>
                        <div class="flex items-center rounded border px-3 py-2 focus-within:ring-2 focus-within:ring-blue-400">
                            <i class="fa fa-envelope mr-3 text-gray-400"></i>
                            <input
                                type="email"
                                v-model="form.email"
                                class="w-full border-0 outline-none focus:ring-0"
                                placeholder="Correo electrónico"
                                required
                            />
                        </div>
                        <div v-if="form.errors.email" class="min-h-[10px]">
                            <p class="text-sm text-red-600">Credenciales incorrectas</p>
                        </div>
                    </div>

                    <!-- Campo de contraseña -->
                    <div>
                        <div class="flex items-center rounded border px-3 py-2 focus-within:ring-2 focus-within:ring-blue-400">
                            <i class="fa fa-lock mr-3 text-gray-400"></i>
                            <input
                                :type="showLoginPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="w-full border-0 text-gray-800 outline-none focus:ring-0"
                                placeholder="Contraseña"
                                required
                            />
                            <button
                                type="button"
                                class="ml-2 text-gray-400 hover:text-gray-600 focus:outline-none"
                                :aria-label="showLoginPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                :title="showLoginPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                @click="showLoginPassword = !showLoginPassword"
                            >
                                <i :class="['fa', showLoginPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center text-gray-500">
                            <input type="checkbox" v-model="form.remember" class="mr-2 rounded border-gray-300 font-medium" />
                            Recordar contraseña
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="h-[50px] w-full rounded-full bg-sky-500 py-2 font-semibold text-white transition hover:bg-sky-400"
                    >
                        <LoaderCircle v-if="form.processing" class="mr-2 inline-block h-4 w-4 animate-spin" />
                        Iniciar sesión
                    </button>

                    <div class="text-center">
                        <a :href="route('password.request')" class="text-sm font-medium text-blue-600 hover:underline"> ¿Olvidó su contraseña? </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
