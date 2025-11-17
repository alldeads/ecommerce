<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Package, Calendar, DollarSign, ShoppingBag } from 'lucide-vue-next'

interface OrderItem {
  id: number
  product_id: number
  quantity: number
  price: string
  subtotal: string
  product: {
    id: number
    name: string
    sku: string
    image_url: string | null
  }
}

interface Order {
  id: number
  order_number: string
  total_amount: string
  status: string
  customer_email: string
  customer_name: string
  shipping_address: string | null
  created_at: string
  order_items: OrderItem[]
}

interface Props {
  orders: Order[]
}

const props = defineProps<Props>()

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'completed':
      return 'default'
    case 'processing':
      return 'secondary'
    case 'cancelled':
      return 'destructive'
    default:
      return 'outline'
  }
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewOrderDetails = (orderId: number) => {
  router.visit(`/orders/${orderId}`)
}

const continueShopping = () => {
  router.visit('/products')
}
</script>

<template>
  <AppLayout>
    <Head title="Order History" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">
          Order History
        </h1>

        <!-- No Orders -->
        <div v-if="orders.length === 0" class="text-center py-12">
          <ShoppingBag class="h-24 w-24 mx-auto text-gray-400 mb-4" />
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            No orders yet
          </h2>
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            Start shopping to create your first order!
          </p>
          <Button @click="continueShopping">
            Browse Products
          </Button>
        </div>

        <!-- Orders List -->
        <div v-else class="space-y-6">
          <Card v-for="order in orders" :key="order.id" class="cursor-pointer hover:shadow-lg transition-shadow">
            <CardHeader @click="viewOrderDetails(order.id)">
              <div class="flex items-start justify-between">
                <div>
                  <CardTitle class="text-xl mb-2">
                    Order #{{ order.order_number }}
                  </CardTitle>
                  <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-1">
                      <Calendar class="h-4 w-4" />
                      {{ formatDate(order.created_at) }}
                    </div>
                    <div class="flex items-center gap-1">
                      <Package class="h-4 w-4" />
                      {{ order.order_items.length }} item{{ order.order_items.length !== 1 ? 's' : '' }}
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <Badge :variant="getStatusVariant(order.status)" class="mb-2">
                    {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                  </Badge>
                  <div class="flex items-center gap-1 text-2xl font-bold text-primary">
                    <DollarSign class="h-5 w-5" />
                    {{ parseFloat(order.total_amount).toFixed(2) }}
                  </div>
                </div>
              </div>
            </CardHeader>

            <CardContent @click="viewOrderDetails(order.id)">
              <!-- Order Items Preview -->
              <div class="space-y-3">
                <div
                  v-for="item in order.order_items.slice(0, 3)"
                  :key="item.id"
                  class="flex items-center gap-3 text-sm"
                >
                  <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden flex-shrink-0">
                    <img
                      v-if="item.product.image_url"
                      :src="item.product.image_url"
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                      No img
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="font-medium">{{ item.product.name }}</p>
                    <p class="text-gray-600 dark:text-gray-400">
                      Qty: {{ item.quantity }} × ${{ parseFloat(item.price).toFixed(2) }}
                    </p>
                  </div>
                  <p class="font-semibold">
                    ${{ parseFloat(item.subtotal).toFixed(2) }}
                  </p>
                </div>

                <p v-if="order.order_items.length > 3" class="text-sm text-gray-600 dark:text-gray-400 text-center">
                  + {{ order.order_items.length - 3 }} more item{{ order.order_items.length - 3 !== 1 ? 's' : '' }}
                </p>
              </div>

              <!-- Shipping Address -->
              <div v-if="order.shipping_address" class="mt-4 pt-4 border-t">
                <p class="text-sm font-semibold mb-1">Shipping Address:</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ order.shipping_address }}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
