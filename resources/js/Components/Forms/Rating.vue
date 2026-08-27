<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0,
    },
    max: {
        type: Number,
        default: 5,
    },
    size: {
        type: String,
        default: 'md',
    },
    color: {
        type: String,
        default: null,
    },
});

const name = `rating-${Math.random().toString(36).slice(2, 8)}`;

const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', Number(val));
    },
});

const getSizeClass = (size) => {
    switch (size) {
        case 'xs':
            return 'rating-xs';
        case 'sm':
            return 'rating-sm';
        case 'lg':
            return 'rating-lg';
        case 'xl':
            return 'rating-xl';
        default:
            return 'rating-md';
    }
};

const classes = computed(() => ['rating', getSizeClass(props.size)].join(' '));
</script>

<template>
    <div :class="classes" role="radiogroup">
        <template v-for="i in props.max" :key="i">
            <input type="radio" :name="name" :value="i" v-model="proxyValue" class="mask mask-star-2 bg-orange-400" />
        </template>
    </div>
</template>
