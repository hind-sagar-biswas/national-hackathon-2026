<script setup>
import { Head } from '@inertiajs/vue3';
import Banner from '@/Components/Utils/Banner.vue';
import SideBar from '@/Components/Nav/SideBar.vue';
import { Menu } from 'lucide-vue-next';

defineProps({
    title: String,
});
</script>

<template>
    <div>

        <Head :title="title" />

        <Banner />

        <SideBar>
            <div class="min-h-screen bg-base-300">
                <!-- Page Heading -->
                <header v-if="$slots.header || title"
                    class="bg-base-100 shadow-sm min-h-24 flex justify-start gap-5 items-center">

                    <label for="navbar" class="px-5 drawer-button cursor-pointer">
                        <Menu class="size-6" />
                    </label>
                    <div class="max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header">
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                {{ title }}
                            </h2>
                        </slot>
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </SideBar>
    </div>
</template>
