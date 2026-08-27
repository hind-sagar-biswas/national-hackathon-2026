<script setup>
import { watch } from 'vue'
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
    value: {
        type: Number,
        default: 0
    },
    max: {
        type: Number,
        default: 100,
    },
    transition: {
        type: Boolean,
        default: true,
    },
    duration: {
        type: Number,
        default: 500,
    },
    showLabel: {
        type: Boolean,
        default: true
    }
})

const displayValue = ref(0)

onMounted(() => {
    if (props.transition) {
        setTimeout(() => {
            displayValue.value = props.value
        }, 50)
    } else {
        displayValue.value = props.value
    }
})

watch(() => props.value, (newVal) => {
    displayValue.value = newVal
})

const percentage = computed(() => Math.round((displayValue.value / props.max) * 100))

const colorClass = computed(() => {
    if (percentage.value < 30) return 'bg-error'
    if (percentage.value < 70) return 'bg-warning'
    return 'bg-success'
})
</script>

<template>
    <div class="w-full">
        <div v-if="showLabel" class="flex justify-between mb-1 ml-1">
            <span class="text-sm font-medium text-base-content uppercase tracking-wider">Progress</span>
            <span class="text-sm font-medium text-base-content">{{ percentage }}%</span>
        </div>

        <div class="relative w-full h-2 bg-base-300 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all ease-out" :class="colorClass" :style="{
                width: `${percentage}%`,
                transitionDuration: transition ? `${duration}ms` : '0ms'
            }"></div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure the transition is butter-smooth */
.transition-all {
    transition-property: width;
}
</style>