<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: String,
    rows: {
        type: [Number, String],
        default: 3,
    },
    color: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
});

const getColorClass = (color) => {
    switch (color) {
        case 'ghost':
            return 'textarea-ghost';
        case 'neutral':
            return 'textarea-neutral';
        case 'primary':
            return 'textarea-primary';
        case 'secondary':
            return 'textarea-secondary';
        case 'accent':
            return 'textarea-accent';
        case 'info':
            return 'textarea-info';
        case 'success':
            return 'textarea-success';
        case 'warning':
            return 'textarea-warning';
        case 'error':
            return 'textarea-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'textarea-xs';
        case 'sm':
            return 'textarea-sm';
        case 'lg':
            return 'textarea-lg';
        case 'xl':
            return 'textarea-xl';
        default:
            return 'textarea-md';
    }
};

const classes = computed(() => ['textarea', getColorClass(props.color), getSizeClass(props.size)].join(' '));
</script>

<template>
    <textarea :class="classes" :rows="rows" :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)" />
</template>
