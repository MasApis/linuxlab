<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout active-tab="register">
        <Head title="Daftar Akun - LinuxLab" />

        <!-- Header Teks Form -->
        <div class="mb-6">
            <h2 class="font-display text-[28px] font-extrabold text-ink tracking-tight">Mulai Belajar Sekarang</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Daftarkan akun gratis untuk mengakses materi modul dan terminal Linux nyata di browser.
            </p>
        </div>

        <!-- Formulir Pendaftaran -->
        <form @submit.prevent="submit" class="space-y-4">
            <!-- Nama Lengkap -->
            <FormInput
                id="name"
                v-model="form.name"
                label="Nama Lengkap"
                placeholder="contoh: Linus Torvalds"
                :error="form.errors.name"
                required
                autofocus
                autocomplete="name"
            >
                <template #prefix>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </template>
            </FormInput>

            <!-- Username -->
            <FormInput
                id="username"
                v-model="form.username"
                label="Username"
                placeholder="contoh: linus_torvalds"
                :error="form.errors.username"
                hint="Huruf, angka, dan garis bawah (_) tanpa spasi"
                required
                is-mono
                autocomplete="username"
            >
                <template #prefix>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </template>
            </FormInput>

            <!-- Email -->
            <FormInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                placeholder="nama@domain.com"
                :error="form.errors.email"
                required
                autocomplete="email"
            >
                <template #prefix>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </template>
            </FormInput>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-text-muted mb-1.5">
                    Password <span class="text-brand-danger ml-0.5">*</span>
                </label>
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
                        placeholder="Minimal 8 karakter"
                        required
                        class="w-full rounded-input border-2 bg-surface-card text-text-primary placeholder:text-text-dim transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary text-sm pl-9 pr-12 py-3"
                        :class="form.errors.password
                            ? 'border-brand-danger focus:border-brand-danger focus:ring-brand-danger/30'
                            : 'border-border focus:border-brand-primary focus:ring-brand-primary/30'"
                        autocomplete="new-password"
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

            <!-- Konfirmasi Password -->
            <FormInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                label="Konfirmasi Password"
                placeholder="Ulangi password di atas"
                :error="form.errors.password_confirmation"
                required
                is-mono
                autocomplete="new-password"
            >
                <template #prefix>
                    <svg class="w-4 h-4 text-text-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </template>
            </FormInput>

            <!-- Tombol Submit -->
            <AppButton
                type="submit"
                variant="primary"
                size="lg"
                class="w-full justify-center mt-2"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Daftar Akun Baru
            </AppButton>

            <!-- Tautan Bawah -->
            <div class="text-center pt-2">
                <p class="text-xs sm:text-sm text-text-muted">
                    Sudah memiliki akun?
                    <Link :href="route('login')" class="min-h-11 inline-flex items-center text-brand-tertiary-light font-extrabold hover:underline ml-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button">
                        Masuk di sini
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
