<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Logo/AuthenticationCardLogo.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import FormField from '@/Components/Forms/FormField.vue';
import Button from '@/Components/Buttons/Button.vue';
import Password from '@/Components/Forms/Password.vue';
import { update } from '@/routes/password';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(update, {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Reset Password" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <FormField name="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required autofocus
                    autocomplete="username" />
            </FormField>

            <FormField name="password" :error="form.errors.password" class="mt-4">
                <Password id="password" v-model="form.password" class="mt-1 w-full" autocomplete="new-password" />
            </FormField>

            <FormField name="password_confirmation" label="Confirm Password" :error="form.errors.password_confirmation"
                class="mt-4">
                <Password id="password_confirmation" v-model="form.password_confirmation" class="mt-1 w-full"
                    autocomplete="new-password" />
            </FormField>

            <div class="flex items-center justify-end mt-4">
                <Button color="primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Reset Password
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
