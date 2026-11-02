<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <UserLayout>
        <Head title="Profil Pengguna - LinuxLab" />

        <div class="py-8 max-w-4xl mx-auto space-y-6">
            <!-- Header Page -->
            <div>
                <h1 class="font-display text-2xl font-bold text-ink tracking-tight">Pengaturan Profil</h1>
                <p class="text-sm text-text-muted mt-1">
                    Kelola identitas akun dan keamanan kata sandi Anda.
                </p>
            </div>

            <!-- Form Edit Profile -->
            <AppCard>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </AppCard>

            <!-- Form Ubah Password -->
            <AppCard>
                <UpdatePasswordForm />
            </AppCard>
        </div>
    </UserLayout>
</template>
