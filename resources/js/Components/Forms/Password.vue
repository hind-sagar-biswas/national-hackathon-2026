<script setup>
import { EyeOff, Eye } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: String,
    color: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
    id: {
        type: String,
        default: null,
    },
    autocomplete: {
        type: String,
        default: 'current-password',
    }
});

defineEmits(['update:modelValue']);

const hide = ref(true);
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
const getBtnColorClass = (color) => {
    switch (color) {
        case 'neutral':
            return 'btn-neutral';
        case 'primary':
            return 'btn-primary';
        case 'secondary':
            return 'btn-secondary';
        case 'accent':
            return 'btn-accent';
        case 'info':
            return 'btn-info';
        case 'success':
            return 'btn-success';
        case 'warning':
            return 'btn-warning';
        case 'error':
            return 'btn-error';
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
    const list = ['join-item flex-grow input', getColorClass(props.color), getSizeClass(props.size)];
    return list.join(' ');
});

const btnClasses = computed(() => {
    const list = ['join-item btn btn-soft', getBtnColorClass(props.color)];
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
    <div class="join">
        <input :type="hide ? 'password' : 'text'" ref="input" :class="classes" :value="modelValue" :id="id"
            @input="$emit('update:modelValue', $event.target.value)" required :autocomplete="autocomplete">
        <button :class="btnClasses" @click="hide = !hide" type="button">
            <Eye class="h-5 w-5" v-if="hide" />
            <EyeOff class="h-5 w-5" v-else />
        </button>
    </div>
</template>
