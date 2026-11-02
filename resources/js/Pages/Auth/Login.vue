<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    canResetPassword?: boolean;
    status?: string;
    success?: string;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout active-tab="login">
        <Head title="Masuk - LinuxLab" />

        <div class="mb-6">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-ink tracking-tight">Selamat Datang Kembali</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Masukkan email atau username untuk melanjutkan belajar.
            </p>
        </div>

        <!-- Flash Notifikasi Sukses / Status -->
        <div
            v-if="success || status"
            class="mb-4 p-3 rounded-button bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light text-xs sm:text-sm flex items-center gap-2"
        >
            <svg class="w-4 h-4 shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ success || status }}</span>
        </div>

        <!-- Formulir Masuk -->
        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email atau Username -->
            <FormInput
                id="login"
                v-model="form.login"
                label="Email atau Username"
                placeholder="contoh: penguin_user atau nama@email.com"
                :error="form.errors.login || (form.errors as Record<string, string | undefined>).email"
                required
                autofocus
            >
                <template #prefix>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </template>
            </FormInput>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between gap-3 mb-1.5">
                    <label for="password" class="text-xs font-semibold text-text-muted">
                        Password <span class="text-brand-danger ml-0.5">*</span>
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="min-h-11 inline-flex items-center text-xs font-medium text-brand-primary-light hover:underline transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button"
                    >
                        Lupa password?
                    </Link>
                </div>
                <div class="relative flex items-center">
                    <div class="absolute left-3 flex items-center pointer-events-none text-text-dim">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        placeholder="••••••••"
                        required
                        class="w-full rounded-input border-2 bg-surface-card text-text-primary placeholder:text-text-dim transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary text-sm pl-9 pr-12 py-3"
                        :class="form.errors.password
                            ? 'border-brand-danger focus:border-brand-danger focus:ring-brand-danger/30'
                            : 'border-border focus:border-brand-primary focus:ring-brand-primary/30'"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-1 top-1/2 -translate-y-1/2 min-h-10 min-w-10 flex items-center justify-center text-text-dim hover:text-text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        :aria-pressed="showPassword"
                    >
                        <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-xs text-brand-danger mt-1">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Ingat Saya -->
            <div class="flex items-center">
                <label class="flex min-h-11 items-center gap-2 cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-xs sm:text-sm text-text-muted">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Tombol Masuk -->
            <AppButton
                type="submit"
                variant="primary"
                size="md"
                class="w-full justify-center"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Masuk ke LinuxLab
            </AppButton>

            <!-- Tautan Bawah -->
            <div class="text-center pt-2">
                <p class="text-xs sm:text-sm text-text-muted">
                    Belum punya akun?
                    <Link :href="route('register')" class="min-h-11 inline-flex items-center text-brand-primary-light font-semibold hover:underline ml-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">
                        Daftar sekarang
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
