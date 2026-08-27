<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: [String, Number, Boolean],
        default: null,
    },
    name: {
        type: String,
        default: null,
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

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});

const getColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'radio-neutral';
        case 'primary':
            return 'radio-primary';
        case 'secondary':
            return 'radio-secondary';
        case 'accent':
            return 'radio-accent';
        case 'info':
            return 'radio-info';
        case 'success':
            return 'radio-success';
        case 'warning':
            return 'radio-warning';
        case 'error':
            return 'radio-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'radio-xs';
        case 'sm':
            return 'radio-sm';
        case 'lg':
            return 'radio-lg';
        case 'xl':
            return 'radio-xl';
        default:
            return 'radio-md';
    }
};

const classes = computed(() => {
    const list = ['radio', getColorClass(props.color), getSizeClass(props.size)];
    return list.join(' ');
});
</script>

<template>
    <input v-model="proxyChecked" type="radio" :value="value" :name="name" :class="classes">
</template>
