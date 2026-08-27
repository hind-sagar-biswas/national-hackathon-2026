import { ref, onMounted, onUnmounted, computed } from 'vue';

export function useMonitorSize(mobileBreakpoint = 768) { // Default breakpoint
    const width = ref(window.innerWidth);

    const handleResize = () => {
        width.value = window.innerWidth;
    };

    onMounted(() => {
        window.addEventListener('resize', handleResize);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize);
    });

    const isMobile = computed(() => width.value <= mobileBreakpoint);

    return { width, isMobile };
}
