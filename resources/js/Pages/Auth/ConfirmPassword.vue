<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout hide-tabs>
        <Head title="Konfirmasi Password - LinuxLab" />

        <div class="mb-6">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-ink tracking-tight">Konfirmasi Password</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Ini area aman aplikasi. Masukkan password Anda untuk melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <FormInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="••••••••"
                :error="form.errors.password"
                required
                autofocus
                autocomplete="current-password"
            />

            <AppButton
                type="submit"
                variant="primary"
                size="md"
                class="w-full justify-center"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Konfirmasi
            </AppButton>
        </form>
    </AuthLayout>
</template>
