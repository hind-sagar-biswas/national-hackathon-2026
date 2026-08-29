<script setup>
import Layout from '@/Layouts/AppLayout.vue';
import { login, register, dashboard } from '@/routes';
import { Link } from '@inertiajs/vue3';
import {
    ShieldCheck,
    ArrowRight,
    Wallet,
    HandCoins,
    Receipt,
    Split,
    Scale,
    Lock,
    Zap,
    CheckCircle2,
    Building2,
    ShieldAlert,
    CreditCard,
    TrendingUp,
    Send,
    Check,
    Globe,
    Layers
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
    layout: (props) => [Layout, { title: 'National Digital Banking & Micro-Credit', canLogin: props.canLogin, canRegister: props.canRegister }],
});

const bankingServices = [
    {
        title: 'Instant P2P Money Transfers',
        description: 'Zero-latency wallet-to-wallet transfers with automated fraud detection and OTP verification.',
        icon: Wallet,
        badge: 'Instant Settlement',
    },
    {
        title: 'Peer-to-Peer Micro-Credit & Loans',
        description: 'Direct peer loan requests with repayment tracking, due date reminders, and lender debt waiving.',
        icon: HandCoins,
        badge: 'Micro-Credit',
    },
    {
        title: 'Smart Bill Splitting Engine',
        description: 'Split restaurant bills or shared expenses among group members with automated invitations.',
        icon: Split,
        badge: 'Group Finance',
    },
    {
        title: 'Money Requests & Pre-Hold',
        description: 'Send invoices with optional fund pre-reservations to guarantee settlement upon approval.',
        icon: Receipt,
        badge: 'Guaranteed Funds',
    },
    {
        title: 'Double-Entry Ledger Audit',
        description: 'Cryptographically verified debit/credit balance sheets for complete financial transparency.',
        icon: ShieldCheck,
        badge: 'Audit Verified',
    },
    {
        title: 'Zero-Sum Mathematical Reconciliation',
        description: 'Real-time background integrity checks ensuring platform liabilities and reserves balance to zero.',
        icon: Scale,
        badge: 'Core Engine',
    },
];

const securityFeatures = [
    '256-Bit Financial Encryption & Idempotency Key Protection',
    'Automated Risk Engine & Suspicious Transaction OTP Challenges',
    'Real-time WebSocket Push Notifications powered by Laravel Reverb',
    'Spatie Fine-Grained Role & Permission Access Controls',
];
</script>

<template>
    <div class="bg-base-100 text-base-content min-h-screen font-sans">
        
        <!-- Clean, Professional Hero Section (No Gradient Background) -->
        <section class="bg-base-100 border-b border-base-200 py-16 lg:py-24 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Column: Professional Copy & Actions (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Minimalist Badge -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-base-200 border border-base-300 text-xs font-semibold text-base-content/80">
                            <ShieldCheck class="size-4 text-primary" />
                            <span>National Core Banking & Micro-Credit Portal</span>
                        </div>

                        <!-- Solid Clean Headline -->
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-base-content tracking-tight leading-tight">
                            Core Banking & Peer-to-Peer Micro-Credit Engine
                        </h1>

                        <!-- Clean Subtitle -->
                        <p class="text-base sm:text-lg text-base-content/75 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                            A secure, audited digital banking platform featuring instant wallet transfers, micro-credit loans, group bill splits, and double-entry balance sheet reconciliation.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start items-center pt-2">
                            <Link v-if="$page.props.auth.user" :href="dashboard()" class="btn btn-primary btn-md sm:btn-lg font-semibold px-6 w-full sm:w-auto">
                                Go to Dashboard
                                <ArrowRight class="size-4 ms-1" />
                            </Link>
                            <template v-else>
                                <Link v-if="canRegister" :href="register()" class="btn btn-primary btn-md sm:btn-lg font-semibold px-6 w-full sm:w-auto">
                                    Open Free Account
                                    <ArrowRight class="size-4 ms-1" />
                                </Link>
                                <Link v-if="canLogin" :href="login()" class="btn btn-outline btn-md sm:btn-lg font-semibold px-6 w-full sm:w-auto">
                                    Sign In
                                </Link>
                            </template>
                        </div>

                        <!-- Clean Key Metrics -->
                        <div class="pt-8 grid grid-cols-3 gap-6 max-w-lg mx-auto lg:mx-0 border-t border-base-200">
                            <div>
                                <span class="block text-xl font-bold text-base-content">0.00 BDT</span>
                                <span class="text-xs text-base-content/60">Discrepancy</span>
                            </div>
                            <div>
                                <span class="block text-xl font-bold text-base-content">Zero-Sum</span>
                                <span class="text-xs text-base-content/60">Ledger Audit</span>
                            </div>
                            <div>
                                <span class="block text-xl font-bold text-base-content">256-Bit</span>
                                <span class="text-xs text-base-content/60">Encryption</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Professional Financial Card Preview (5 Cols) -->
                    <div class="lg:col-span-5">
                        <div class="bg-base-200/60 border border-base-300 p-6 rounded-xl shadow-xs space-y-5">
                            
                            <!-- Header Bar -->
                            <div class="flex items-center justify-between border-b border-base-300/80 pb-4">
                                <div class="flex items-center gap-2">
                                    <Building2 class="size-5 text-primary" />
                                    <span class="font-bold text-sm text-base-content">Wallet Account Overview</span>
                                </div>
                                <span class="badge badge-success badge-sm font-semibold">VERIFIED</span>
                            </div>

                            <!-- Balance Card -->
                            <div class="bg-base-100 border border-base-300 p-4 rounded-lg space-y-1">
                                <span class="text-xs text-base-content/60 block font-medium">Cleared Ledger Balance</span>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-2xl font-bold text-base-content">124,850.00 BDT</span>
                                    <span class="text-xs text-success font-semibold flex items-center gap-0.5">
                                        <TrendingUp class="size-3.5 inline" /> +12.4%
                                    </span>
                                </div>
                            </div>

                            <!-- Recent Transaction Mini List -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-base-content/60 block">Recent System Activity</span>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-base-100 border border-base-300/80">
                                    <div class="flex items-center gap-2.5">
                                        <div class="bg-success/10 text-success p-1.5 rounded">
                                            <HandCoins class="size-4" />
                                        </div>
                                        <div>
                                            <span class="text-xs font-semibold block text-base-content">Money Request Settled</span>
                                            <span class="text-[10px] text-base-content/50">Ref: TRX-998412</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-success">+5,000.00 BDT</span>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-base-100 border border-base-300/80">
                                    <div class="flex items-center gap-2.5">
                                        <div class="bg-primary/10 text-primary p-1.5 rounded">
                                            <Send class="size-4" />
                                        </div>
                                        <div>
                                            <span class="text-xs font-semibold block text-base-content">P2P Wallet Transfer</span>
                                            <span class="text-[10px] text-base-content/50">Ref: TRX-110293</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-error">-1,250.00 BDT</span>
                                </div>
                            </div>

                            <!-- Footer Status -->
                            <div class="pt-3 border-t border-base-300/80 flex items-center justify-between text-xs text-base-content/60">
                                <span>Zero-Sum Invariant Check</span>
                                <span class="font-mono font-bold text-success">PASSED (0.00)</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-base-200/40">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-base-content mb-3">
                        Comprehensive Digital Banking Services
                    </h2>
                    <p class="text-base-content/70 text-base max-w-2xl mx-auto">
                        Engineered with mathematical precision, strict security controls, and intuitive financial tools for everyday transactions.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="service in bankingServices" 
                        :key="service.title"
                        class="card bg-base-100 shadow-xs border border-base-300"
                    >
                        <div class="card-body p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-10 h-10 rounded-lg bg-base-200 text-primary flex items-center justify-center">
                                    <component :is="service.icon" class="size-5" />
                                </div>
                                <span class="badge badge-sm badge-outline font-medium">{{ service.badge }}</span>
                            </div>
                            <h3 class="card-title text-base font-bold text-base-content mb-2">
                                {{ service.title }}
                            </h3>
                            <p class="text-xs text-base-content/70 leading-relaxed">
                                {{ service.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security & Architecture Section -->
        <section class="py-20 px-4 sm:px-6 lg:px-8 bg-base-100">
            <div class="max-w-5xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-base-200 text-base-content/80 font-semibold text-xs mb-4">
                            <ShieldAlert class="size-4 text-warning" /> Security & Compliance First
                        </div>
                        <h2 class="text-3xl font-bold text-base-content mb-4">
                            Built for Enterprise Banking & Integrity
                        </h2>
                        <p class="text-base-content/75 text-sm mb-6 leading-relaxed">
                            Every financial mutation is protected by idempotency middleware to eliminate double-spend attacks, while our risk engine automatically triggers OTP verification when unusual transfer patterns are detected.
                        </p>

                        <div class="space-y-3">
                            <div v-for="item in securityFeatures" :key="item" class="flex gap-3 items-start">
                                <CheckCircle2 class="size-5 text-success shrink-0 mt-0.5" />
                                <span class="text-sm font-medium text-base-content/85">{{ item }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Security Card -->
                    <div class="bg-base-200/60 border border-base-300 p-6 rounded-xl shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-base-300 pb-4">
                            <div class="flex items-center gap-2">
                                <Building2 class="size-5 text-primary" />
                                <span class="font-bold text-sm">General Ledger Audit Status</span>
                            </div>
                            <span class="badge badge-success badge-sm font-semibold">VERIFIED</span>
                        </div>

                        <div class="space-y-2 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Platform Equity:</span>
                                <span class="font-bold text-base-content">SYS-EQUITY (Balanced)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Double-Entry Check:</span>
                                <span class="font-bold text-success">Passed (0 Discrepancy)</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Idempotency Guard:</span>
                                <span class="font-bold text-info">Active (X-Idempotency-Key)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Banner -->
     

    </div>
</template>
