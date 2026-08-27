<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    placeholder: {
        type: String,
        default: 'Search',
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
    <label class="input w-full" :class="classes">
        <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
            </g>
        </svg>
        <input type="search" id="search-box" name="search" class="grow" :placeholder="placeholder" ref="input" :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)" v-shortkey.focus="['ctrl', 'k']" />
        <kbd class="kbd">⌘</kbd>
        <kbd class="kbd">K</kbd>
    </label>
</template>
