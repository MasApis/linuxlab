<script setup lang="ts">
import { computed } from 'vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import AppButton from '@/Components/AppButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout hide-tabs>
        <Head title="Verifikasi Email - LinuxLab" />

        <div class="mb-6">
            <h2 class="font-display text-xl sm:text-2xl font-bold text-ink tracking-tight">Verifikasi Email</h2>
            <p class="text-xs sm:text-sm text-text-muted mt-1">
                Terima kasih sudah mendaftar. Klik tautan verifikasi yang kami kirim ke email Anda sebelum mulai belajar. Belum menerima email? Kami kirim ulang.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 p-3 rounded-button bg-brand-primary/10 border border-brand-primary/30 text-brand-primary-light text-xs sm:text-sm flex items-center gap-2"
            role="status"
        >
            <svg class="w-4 h-4 shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Tautan verifikasi baru sudah dikirim ke email Anda.</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <AppButton
                type="submit"
                variant="primary"
                size="md"
                class="w-full justify-center"
                :loading="form.processing"
                :disabled="form.processing"
            >
                Kirim Ulang Email Verifikasi
            </AppButton>

            <div class="text-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="min-h-11 inline-flex items-center text-xs sm:text-sm font-medium text-text-muted hover:text-text-primary underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary rounded-button"
                >
                    Keluar
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
