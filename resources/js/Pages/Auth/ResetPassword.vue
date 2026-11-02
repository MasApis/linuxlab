<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout hide-tabs>
        <Head title="Atur Ulang Password - LinuxLab" />

        <div class="mb-6">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-ink tracking-tight">Atur Ulang Password</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Buat password baru untuk akun Anda.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <FormInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                placeholder="nama@domain.com"
                :error="form.errors.email"
                required
                autofocus
                autocomplete="username"
            />

            <FormInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password Baru"
                placeholder="Minimal 8 karakter"
                :error="form.errors.password"
                required
                autocomplete="new-password"
            />

            <FormInput
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                label="Konfirmasi Password Baru"
                placeholder="Ulangi password baru"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
            />

            <AppButton
                type="submit"
                variant="primary"
                size="md"
                class="w-full justify-center"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Simpan Password Baru
            </AppButton>
        </form>
    </AuthLayout>
</template>
