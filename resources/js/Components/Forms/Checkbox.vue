<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        type: String,
        default: null,
    },
    color: {
        type: String,
        default: 'primary',
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
            return 'checkbox-neutral';
        case 'primary':
            return 'checkbox-primary';
        case 'secondary':
            return 'checkbox-secondary';
        case 'accent':
            return 'checkbox-accent';
        case 'info':
            return 'checkbox-info';
        case 'success':
            return 'checkbox-success';
        case 'warning':
            return 'checkbox-warning';
        case 'error':
            return 'checkbox-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'checkbox-xs';
        case 'sm':
            return 'checkbox-sm';
        case 'lg':
            return 'checkbox-lg';
        case 'xl':
            return 'checkbox-xl';
        default:
            return 'checkbox-md';
    }
};

const classes = computed(() => {
    const list = ['checkbox', getColorClass(props.color), getSizeClass(props.size)];
    return list.join(' ');
});
</script>


<template>
    <input v-model="proxyChecked" type="checkbox" :value="value" :class="classes">
</template>
