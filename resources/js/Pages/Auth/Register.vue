<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/Logo/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Forms/Checkbox.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import Password from '@/Components/Forms/Password.vue';
import FormField from '@/Components/Forms/FormField.vue';
import Button from '@/Components/Buttons/Button.vue';
import { show as showTerms } from '@/routes/terms';
import { show as showPolicy } from '@/routes/policy';
import { login, register } from '@/routes';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(register(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <FormField name="name" :error="form.errors.name">
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required autofocus
                    autocomplete="name" />
            </FormField>

            <FormField name="email" :error="form.errors.email" class="mt-4">
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                    autocomplete="username" />
            </FormField>

            <FormField name="phone" :error="form.errors.phone" class="mt-4">
                <TextInput id="phone" v-model="form.phone" type="tel" class="mt-1 block w-full" required
                    placeholder="01XXXXXXXXX" autocomplete="phone" />
            </FormField>

            <FormField name="password" :error="form.errors.password" class="mt-4">
                <Password id="password" v-model="form.password" class="mt-1 w-full" autocomplete="new-password" />
            </FormField>

            <FormField name="password_confirmation" label="Confirm Password" :error="form.errors.password_confirmation"
                class="mt-4">
                <Password id="password_confirmation" v-model="form.password_confirmation" class="mt-1 w-full"
                    autocomplete="new-password" />
            </FormField>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the <a target="_blank" :href="showTerms.url()"
                                class="text-sm link link-primary">Terms
                                of Service</a> and <a target="_blank" :href="showPolicy.url()"
                                class="text-sm link link-primary">Privacy
                                Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="login()"
                    class="text-sm link link-primary">
                    Already registered?
                </Link>

                <Button color="primary" class="ms-4" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Register
                </Button>
            </div>
        </form>
    </AuthenticationCard>
</template>
