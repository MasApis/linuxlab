<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout hide-tabs>
        <Head title="Lupa Password - LinuxLab" />

        <div class="mb-6">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-ink tracking-tight">Lupa Password</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 p-3 rounded-button bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light text-xs sm:text-sm flex items-center gap-2"
            role="status"
        >
            <svg class="w-4 h-4 shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ status }}</span>
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

            <AppButton
                type="submit"
                variant="primary"
                size="md"
                class="w-full justify-center"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Kirim Tautan Reset
            </AppButton>
        </form>
    </AuthLayout>
</template>
