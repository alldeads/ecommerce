<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ShoppingCart, DollarSign, Package, TrendingUp } from 'lucide-vue-next';

interface Stats {
    total_orders: number;
    total_spent: string;
    orders_this_month: number;
    total_products: number;
}

interface OrderPerMonth {
    month: string;
    count: number;
    total: string;
}

interface RecentOrder {
    id: number;
    status: string;
    total_amount: string;
    created_at: string;
    items_count: number;
}

interface Props {
    stats: Stats;
    ordersPerMonth: OrderPerMonth[];
    recentOrders: RecentOrder[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatMonth = (monthStr: string) => {
    const [year, month] = monthStr.split('-');
    const date = new Date(parseInt(year), parseInt(month) - 1);
    return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'text-yellow-600 bg-yellow-50 dark:bg-yellow-950 dark:text-yellow-400',
        completed: 'text-green-600 bg-green-50 dark:bg-green-950 dark:text-green-400',
        cancelled: 'text-red-600 bg-red-50 dark:bg-red-950 dark:text-red-400',
    };
    return colors[status] || 'text-gray-600 bg-gray-50 dark:bg-gray-950 dark:text-gray-400';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Stats Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <!-- Total Orders -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Orders</CardTitle>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_orders }}</div>
                        <p class="text-xs text-muted-foreground">All time orders</p>
                    </CardContent>
                </Card>

                <!-- Total Spent -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Spent</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">${{ stats.total_spent }}</div>
                        <p class="text-xs text-muted-foreground">Lifetime spending</p>
                    </CardContent>
                </Card>

                <!-- Orders This Month -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.orders_this_month }}</div>
                        <p class="text-xs text-muted-foreground">Orders in current month</p>
                    </CardContent>
                </Card>

                <!-- Available Products -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Products</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_products }}</div>
                        <p class="text-xs text-muted-foreground">Available to purchase</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Orders Per Month & Recent Orders -->
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Orders Per Month -->
                <Card>
                    <CardHeader>
                        <CardTitle>Orders Per Month</CardTitle>
                        <CardDescription>Your order history for the last 6 months</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="ordersPerMonth.length > 0" class="space-y-4">
                            <div
                                v-for="month in ordersPerMonth"
                                :key="month.month"
                                class="flex items-center justify-between border-b pb-3 last:border-0"
                            >
                                <div>
                                    <p class="font-medium">{{ formatMonth(month.month) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ month.count }} orders</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-primary">${{ month.total }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No orders in the last 6 months
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Orders -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Orders</CardTitle>
                        <CardDescription>Your 5 most recent orders</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentOrders.length > 0" class="space-y-4">
                            <Link
                                v-for="order in recentOrders"
                                :key="order.id"
                                :href="`/orders/${order.id}`"
                                class="flex items-center justify-between border-b pb-3 last:border-0 hover:bg-accent/50 transition-colors rounded p-2 -m-2"
                            >
                                <div>
                                    <p class="font-medium">Order #{{ order.id }}</p>
                                    <p class="text-sm text-muted-foreground">{{ order.created_at }} • {{ order.items_count }} items</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">${{ order.total_amount }}</p>
                                    <span
                                        :class="getStatusColor(order.status)"
                                        class="text-xs px-2 py-1 rounded-full inline-block mt-1"
                                    >
                                        {{ order.status }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No orders yet
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
