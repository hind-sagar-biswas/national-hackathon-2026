<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: [String, Number, Array],
        default: null,
    },
    options: {
        type: Array,
        default: () => [],
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    placeholder: {
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

const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    },
});

const getColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'select-neutral';
        case 'primary':
            return 'select-primary';
        case 'secondary':
            return 'select-secondary';
        case 'accent':
            return 'select-accent';
        case 'info':
            return 'select-info';
        case 'success':
            return 'select-success';
        case 'warning':
            return 'select-warning';
        case 'error':
            return 'select-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'select-xs';
        case 'sm':
            return 'select-sm';
        case 'lg':
            return 'select-lg';
        case 'xl':
            return 'select-xl';
        default:
            return 'select-md';
    }
};

const classes = computed(() => ['select', getColorClass(props.color), getSizeClass(props.size)].join(' '));
</script>

<template>
    <select :multiple="multiple" :class="classes" v-model="proxyValue">
        <option v-if="placeholder && !multiple" :value="null">{{ placeholder }}</option>
        <option v-for="(opt, idx) in options" :key="idx" :value="opt.value" :disabled="opt.disabled">{{ opt.label }}
        </option>
    </select>
</template>
