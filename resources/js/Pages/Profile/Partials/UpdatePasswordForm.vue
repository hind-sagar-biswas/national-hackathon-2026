<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/Forms/FormSection.vue';
import Password from '@/Components/Forms/Password.vue';
import FormField from '@/Components/Forms/FormField.vue';
import Button from '@/Components/Buttons/Button.vue';
import { update } from '@/routes/user-password';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(update(), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <FormSection @submitted="updatePassword">
        <template #title>
            Update Password
        </template>

        <template #description>
            Ensure your account is using a long, random password to stay secure.
        </template>

        <template #form>
            <FormField name="current_password" label="Current Password" :error="form.errors.current_password"
                class="col-span-6 sm:col-span-4">
                <Password id="current_password" ref="currentPasswordInput" class="mt-1 w-full"
                    v-model="form.current_password" />
            </FormField>

            <FormField name="password" label="New Password" :error="form.errors.password"
                class="col-span-6 sm:col-span-4">
                <Password id="password" ref="passwordInput" class="mt-1 w-full" v-model="form.password"
                    autocomplete="new-password" />
            </FormField>

            <FormField name="password_confirmation" label="Confirm Password" :error="form.errors.password_confirmation"
                class="col-span-6 sm:col-span-4">
                <Password id="password_confirmation" v-model="form.password_confirmation" class="mt-1 w-full"
                    autocomplete="new-password" />
            </FormField>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <Button color="primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </Button>
        </template>
    </FormSection>
</template>
