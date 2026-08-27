<script setup>
import { capitalize } from 'lodash-es';
import InputLabel from './InputLabel.vue';
import InputError from './InputError.vue';

const props = defineProps({
    name: String,
    label: String,
    error: String,
});
</script>

<template>
    <div>
        <InputLabel :for="name" :value="label ?? capitalize(name)" />
        <slot />
        <InputError :message="error" class="mt-2" />
    </div>
</template>