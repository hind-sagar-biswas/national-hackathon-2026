<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Logo/AuthenticationCardLogo.vue';
import Password from '@/Components/Forms/Password.vue';
import FormField from '@/Components/Forms/FormField.vue';
import Button from '@/Components/Buttons/Button.vue';
import { confirm } from '@/routes/password';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(confirm(), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>

    <Head title="Secure Area" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your password before continuing.
        </div>

        <form @submit.prevent="submit">
            <FormField name="password" label="Password" :error="form.errors.password">
                <Password id="password" ref="passwordInput" v-model="form.password" class="mt-1 block w-full" required
                    autocomplete="current-password" autofocus />
            </FormField>

            <div class="flex justify-end mt-4">
                <Button color="primary" class="ms-4" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Confirm
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
