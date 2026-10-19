<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';

const user = usePage().props.auth.user as { name: string; username: string; email: string };

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-white tracking-tight">
                Informasi Profil
            </h2>
            <p class="mt-1 text-sm text-text-muted">
                Perbarui nama lengkap akun LinuxLab Anda.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <!-- Username (Read-only) -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-text-muted mb-1.5">
                    Username
                </label>
                <div class="px-3.5 py-2.5 rounded-input border border-border bg-surface-base text-text-muted font-mono text-sm cursor-not-allowed">
                    @{{ user.username }}
                </div>
                <p class="text-xs text-text-dim mt-1">Username tidak dapat diubah.</p>
            </div>

            <!-- Nama Lengkap -->
            <FormInput
                id="name"
                v-model="form.name"
                label="Nama Lengkap"
                placeholder="Nama Anda"
                :error="form.errors.name"
                required
                autocomplete="name"
            />

            <!-- Email (Read-only/Editable) -->
            <FormInput
                id="email"
                v-model="form.email"
                type="email"
                label="Alamat Email"
                placeholder="Email Anda"
                :error="form.errors.email"
                required
                autocomplete="email"
            />

            <div class="flex items-center gap-4 pt-2">
                <AppButton
                    type="submit"
                    variant="primary"
                    size="md"
                    :is-loading="form.processing"
                    :disabled="form.processing"
                >
                    Simpan Perubahan
                </AppButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm font-medium text-brand-primary-light flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Tersimpan.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
