<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Logo/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Forms/Checkbox.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import Password from '@/Components/Forms/Password.vue';
import FormField from '@/Components/Forms/FormField.vue';
import Button from '@/Components/Buttons/Button.vue';
import { login } from '@/routes';
import { request } from '@/routes/password';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(login(), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <FormField name="email" label="Email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus
                    autocomplete="username" />
            </FormField>

            <FormField class="mt-4" name="password" label="Password" :error="form.errors.password">
                <Password id="password" v-model="form.password" class="mt-1 w-full" />
            </FormField>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link v-if="canResetPassword" :href="request()"
                    class="text-sm link link-primary">
                    Forgot your password?
                </Link>

                <Button color="primary" class="ms-4" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Log in
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
