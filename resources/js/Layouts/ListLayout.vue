<script setup>
import PaginationSkeleton from '@/Components/Skeletons/PaginationSkeleton.vue';
import Pagination from '@/Components/Utils/Pagination.vue';
import { Deferred } from '@inertiajs/vue3';

defineProps({
    list: Object,
    paginated: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>

    <div class="pb-12 pt-4">
        <div class="max-w-400 mx-auto sm:px-6 lg:px-8">

            <div class="mb-5" v-if="$slots.form">
                <slot name="form" />
            </div>

            <div class="bg-base-200 p-4 rounded-box mb-5" v-if="$slots.filter">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-5">
                    <slot name="filter" />
                </div>
            </div>

            <Deferred data="list">
                <template #fallback>
                    <PaginationSkeleton class="my-5" />
                    <div class="mb-5">
                        <div class="skeleton w-full h-50 bg-base-200"></div>
                    </div>
                    <PaginationSkeleton class="my-5" />
                </template>


                <div class="flex flex-col items-center justify-center my-10"
                    v-if="paginated ? list.data.length === 0 : list.length === 0" role="status">
                    <img src="/oops.png" alt="No items available" class="w-48 opacity-50" />
                    <p class="text-base-content/60 italic mt-4">
                        No items found
                    </p>
                </div>
                <template v-else>
                    <Pagination v-if="paginated" :pagination="list.meta.links" class="my-5" />
                    <div class="mb-5">

                        <!-- List Content -->
                        <slot />

                    </div>
                    <Pagination v-if="paginated" :pagination="list.meta.links" class="my-5" />
                </template>
            </Deferred>
        </div>
    </div>

</template>
