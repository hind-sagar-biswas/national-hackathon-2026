<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: null,
    multiple: {
        type: Boolean,
        default: false,
    },
    accept: {
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

const getColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'file-input-neutral';
        case 'primary':
            return 'file-input-primary';
        case 'secondary':
            return 'file-input-secondary';
        case 'accent':
            return 'file-input-accent';
        case 'info':
            return 'file-input-info';
        case 'success':
            return 'file-input-success';
        case 'warning':
            return 'file-input-warning';
        case 'error':
            return 'file-input-error';
        default:
            return '';
    }
};

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'file-input-xs';
        case 'sm':
            return 'file-input-sm';
        case 'lg':
            return 'file-input-lg';
        case 'xl':
            return 'file-input-xl';
        default:
            return 'file-input-md';
    }
};

const classes = computed(() => ['file-input', getColorClass(props.color), getSizeClass(props.size)].join(' '));

const onChange = (e) => {
    const files = e.target.files;
    if (!files) {
        emit('update:modelValue', null);
        return;
    }

    if (props.multiple) {
        emit('update:modelValue', Array.from(files));
    } else {
        emit('update:modelValue', files[0]);
    }
};
</script>

<template>
    <input type="file" :class="classes" :multiple="multiple" :accept="accept" @change="onChange">
</template>
