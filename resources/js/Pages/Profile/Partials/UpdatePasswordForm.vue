<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import FormInput from '@/Components/FormInput.vue';
import AppButton from '@/Components/AppButton.vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
            }
            if (form.errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-bold text-white tracking-tight">
                Perbarui Password
            </h2>
            <p class="mt-1 text-sm text-text-muted">
                Pastikan akun Anda menggunakan password yang kuat dan aman untuk melindungi sesi praktik Anda.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-4">
            <FormInput
                id="current_password"
                v-model="form.current_password"
                type="password"
                label="Password Saat Ini"
                placeholder="••••••••"
                :error="form.errors.current_password"
                required
                is-mono
                autocomplete="current-password"
            />

            <FormInput
                id="password"
                v-model="form.password"
                type="password"
                label="Password Baru"
                placeholder="Minimal 8 karakter"
                :error="form.errors.password"
                required
                is-mono
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
                is-mono
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4 pt-2">
                <AppButton
                    type="submit"
                    variant="primary"
                    size="md"
                    :is-loading="form.processing"
                    :disabled="form.processing"
                >
                    Simpan Password
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
                        <span>Password berhasil diperbarui.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
