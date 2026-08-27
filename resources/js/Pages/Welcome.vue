<script setup>
import Layout from '@/Layouts/AppLayout.vue';
import { login, register, dashboard } from '@/routes';
import { Link } from '@inertiajs/vue3';
import {
    Shield,
    Users,
    Bell,
    Lock,
    Zap,
    BarChart3,
    CheckCircle2,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

defineOptions({
    layout: (props) => [Layout, { title: 'Welcome', canLogin: props.canLogin, canRegister: props.canRegister }],
})

const features = [
    {
        title: 'Role-Based Access Control',
        description: 'Fine-grained permissions and role management system powered by Spatie.',
        icon: Shield,
    },
    {
        title: 'User Management',
        description: 'Comprehensive user administration with filtering, search, and status controls.',
        icon: Users,
    },
    {
        title: 'Real-Time Notifications',
        description: 'Built-in notification system with API endpoints and real-time updates.',
        icon: Bell,
    },
    {
        title: 'Two-Factor Authentication',
        description: 'Secure authentication with 2FA support using TOTP and recovery codes.',
        icon: Lock,
    },
    {
        title: 'Modern Stack',
        description: 'Laravel 13, Vue 3, Inertia.js, Tailwind CSS v4, and daisyUI components.',
        icon: Zap,
    },
    {
        title: 'Analytics Ready',
        description: 'Built-in pagination, rate limiting, and structured API responses.',
        icon: BarChart3,
    },
];
</script>

<template>
    <div class="bg-linear-to-b from-base-200 to-base-100">
        <!-- Hero Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-base-content mb-6">
                    Modern Admin Template for Laravel
                </h1>
                <p class="text-lg sm:text-xl text-base-content/75 mb-10 max-w-2xl mx-auto">
                    A production-ready starter kit with authentication, role-based access control, and a sleek Vue 3
                    interface. Build your next admin panel faster.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link v-if="$page.props.auth.user" :href="dashboard()" class="btn btn-soft btn-lg">
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link v-if="canRegister" :href="register()" class="btn btn-primary btn-lg">
                            Get Started
                            <ArrowRight class="size-5" />
                        </Link>
                        <Link v-if="canLogin" :href="login()" class="btn btn-soft btn-lg">
                            Sign In
                        </Link>
                    </template>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-base-100">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-base-content mb-4">
                        Powerful Features Out of the Box
                    </h2>
                    <p class="text-base-content/70 text-lg max-w-2xl mx-auto">
                        Everything you need to build production-grade admin applications
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <div v-for="feature in features" :key="feature.title"
                        class="card bg-base-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="card-body">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0">
                                    <div
                                        class="flex items-center justify-center h-12 w-12 rounded-lg bg-primary/10 text-primary">
                                        <component :is="feature.icon" class="size-6" />
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title text-lg mb-2">
                                        {{ feature.title }}
                                    </h3>
                                    <p class="text-base-content/70">
                                        {{ feature.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Highlights Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">13</div>
                        <p class="text-base-content/70">Laravel 13 Foundation</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">100%</div>
                        <p class="text-base-content/70">Out of the Box</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">100%</div>
                        <p class="text-base-content/70">Fully Customizable</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-base-200">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-bold text-base-content mb-12 text-center">
                    Built with Modern Technologies
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-base-100 rounded-lg p-6 text-center">
                        <div class="font-semibold text-base-content mb-2">Laravel 13</div>
                        <p class="text-sm text-base-content/60">Backend Framework</p>
                    </div>
                    <div class="bg-base-100 rounded-lg p-6 text-center">
                        <div class="font-semibold text-base-content mb-2">Vue 3</div>
                        <p class="text-sm text-base-content/60">Frontend Framework</p>
                    </div>
                    <div class="bg-base-100 rounded-lg p-6 text-center">
                        <div class="font-semibold text-base-content mb-2">Inertia</div>
                        <p class="text-sm text-base-content/60">SPA Framework</p>
                    </div>
                    <div class="bg-base-100 rounded-lg p-6 text-center">
                        <div class="font-semibold text-base-content mb-2">Tailwind CSS</div>
                        <p class="text-sm text-base-content/60">Styling & daisyUI</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-3xl font-bold text-base-content mb-12 text-center">
                    Why Choose This Template
                </h2>
                <div class="space-y-4">
                    <div class="flex gap-4 items-start">
                        <CheckCircle2 class="size-6 text-primary shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-base-content">Production Ready</h3>
                            <p class="text-base-content/70">Includes comprehensive test coverage and best practices</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <CheckCircle2 class="size-6 text-primary shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-base-content">Fully Documented</h3>
                            <p class="text-base-content/70">Clear setup instructions and comprehensive README</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <CheckCircle2 class="size-6 text-primary shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-base-content">Developer Friendly</h3>
                            <p class="text-base-content/70">Clean code structure and easy to understand patterns</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <CheckCircle2 class="size-6 text-primary shrink-0 mt-1" />
                        <div>
                            <h3 class="font-semibold text-base-content">Active Updates</h3>
                            <p class="text-base-content/70">Maintained with latest Laravel and Vue versions</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-primary/5">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-base-content mb-6">
                    Ready to build something great?
                </h2>
                <p class="text-lg text-base-content/70 mb-10">
                    Start building your admin panel today with this powerful template.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link v-if="$page.props.auth.user" :href="dashboard()" class="btn btn-primary btn-lg">
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link v-if="canRegister" :href="register()" class="btn btn-primary btn-lg">
                            Create Account
                        </Link>
                        <Link v-if="canLogin" :href="login()" class="btn btn-outline btn-lg">
                            Sign In
                        </Link>
                    </template>
                </div>
            </div>
        </section>
    </div>
</template>
