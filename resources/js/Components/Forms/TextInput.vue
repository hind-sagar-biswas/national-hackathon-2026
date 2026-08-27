<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    color: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
});

defineEmits(['update:modelValue']);

const input = ref(null);

const getColorClass = (color) => {
    switch (color) {
        case 'ghost':
            return 'input-ghost';
        case 'neutral':
            return 'input-neutral';
        case 'primary':
            return 'input-primary';
        case 'secondary':
            return 'input-secondary';
        case 'accent':
            return 'input-accent';
        case 'info':
            return 'input-info';
        case 'success':
            return 'input-success';
        case 'warning':
            return 'input-warning';
        case 'error':
            return 'input-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'input-xs';
        case 'sm':
            return 'input-sm';
        case 'lg':
            return 'input-lg';
        case 'xl':
            return 'input-xl';
        default:
            return 'input-md';
    }
};

const classes = computed(() => {
    const list = ['input', getColorClass(props.color), getSizeClass(props.size)];
    return list.join(' ');
});

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input ref="input" :class="classes" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
</template>
