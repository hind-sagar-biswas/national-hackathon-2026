<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: 0,
    },
    min: {
        type: [Number, String],
        default: 0,
    },
    max: {
        type: [Number, String],
        default: 100,
    },
    step: {
        type: [Number, String],
        default: 1,
    },
    color: {
        type: String,
        default: null,
    },
});

const onInput = (e) => {
    const val = e.target.value;
    emit('update:modelValue', Number(val));
};
</script>

<template>
    <input type="range" :min="min" :max="max" :step="step" :value="modelValue" @input="onInput">
</template>
