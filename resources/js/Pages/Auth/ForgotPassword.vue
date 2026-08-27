<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Logo/AuthenticationCardLogo.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import Button from '@/Components/Buttons/Button.vue';
import FormField from '@/Components/Forms/FormField.vue';
import { email } from '@/routes/password';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(email());
};
</script>

<template>

    <Head title="Forgot Password" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset
            link
            that will allow you to choose a new one.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <FormField name="email" label="Email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus
                    autocomplete="username" />
            </FormField>

            <div class="flex items-center justify-end mt-4">
                <Button color="primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Email Password Reset Link
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
