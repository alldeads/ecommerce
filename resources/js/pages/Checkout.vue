<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { CheckCircle } from 'lucide-vue-next'

interface Product {
  id: number
  name: string
  description: string
  price: string
  stock: number
  sku: string
  image_url: string | null
  is_active: boolean
}

interface CartItem {
  product: Product
  quantity: number
}

interface Props {
  user: {
    id: number
    name: string
    email: string
  }
}

const props = defineProps<Props>()

const cart = ref<Record<number, CartItem>>({})
const orderPlaced = ref(false)

onMounted(() => {
  const savedCart = localStorage.getItem('cart')
  if (savedCart) {
    cart.value = JSON.parse(savedCart)
  }

  // Redirect if cart is empty
  if (Object.keys(cart.value).length === 0) {
    router.visit('/cart')
  }
})

const cartItems = computed(() => Object.values(cart.value))

const cartTotal = computed(() => {
  return cartItems.value.reduce((sum: number, item: CartItem) => {
    return sum + (parseFloat(item.product.price) * item.quantity)
  }, 0)
})

const form = useForm({
  items: cartItems.value.map((item: CartItem) => ({
    product_id: item.product.id,
    quantity: item.quantity
  })),
  customer_name: props.user.name,
  customer_email: props.user.email,
  shipping_address: ''
})

const submitOrder = () => {
  // Update items before submitting
  form.items = cartItems.value.map((item: CartItem) => ({
    product_id: item.product.id,
    quantity: item.quantity
  }))

  console.log('Form data before submit:', {
    items: form.items,
    customer_name: form.customer_name,
    customer_email: form.customer_email,
    shipping_address: form.shipping_address
  })

  form.post('/checkout', {
    onSuccess: () => {
      orderPlaced.value = true
      localStorage.removeItem('cart')

      // Redirect to orders page after 2 seconds
      setTimeout(() => {
        router.visit('/orders')
      }, 2000)
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    }
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Checkout" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">
          Checkout
        </h1>

        <!-- Order Success Message -->
        <Alert v-if="orderPlaced" class="mb-8 border-green-500 bg-green-50 dark:bg-green-950">
          <CheckCircle class="h-5 w-5 text-green-500" />
          <AlertDescription class="text-green-700 dark:text-green-300 ml-2">
            Order placed successfully! You will receive a confirmation email shortly. Redirecting to order history...
          </AlertDescription>
        </Alert>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Checkout Form -->
          <div class="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle>Shipping Information</CardTitle>
              </CardHeader>
              <CardContent>
                <form @submit.prevent="submitOrder" class="space-y-6">
                  <div class="space-y-2">
                    <Label for="customer_name">Full Name</Label>
                    <Input
                      id="customer_name"
                      v-model="form.customer_name"
                      type="text"
                      required
                    />
                    <span v-if="form.errors.customer_name" class="text-sm text-red-500">
                      {{ form.errors.customer_name }}
                    </span>
                  </div>

                  <div class="space-y-2">
                    <Label for="customer_email">Email Address</Label>
                    <Input
                      id="customer_email"
                      v-model="form.customer_email"
                      type="email"
                      required
                    />
                    <span v-if="form.errors.customer_email" class="text-sm text-red-500">
                      {{ form.errors.customer_email }}
                    </span>
                  </div>

                  <div class="space-y-2">
                    <Label for="shipping_address">Shipping Address (Optional)</Label>
                    <Textarea
                      id="shipping_address"
                      v-model="form.shipping_address"
                      rows="4"
                      placeholder="Enter your complete shipping address"
                    />
                    <span v-if="form.errors.shipping_address" class="text-sm text-red-500">
                      {{ form.errors.shipping_address }}
                    </span>
                    <span v-if="!form.errors.shipping_address && form.shipping_address" class="text-xs text-green-600">
                      ✓ Address entered ({{ form.shipping_address.length }} characters)
                    </span>
                  </div>

                  <div v-if="form.errors.items" class="text-sm text-red-500">
                    {{ form.errors.items }}
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <Card class="sticky top-4">
              <CardHeader>
                <CardTitle>Order Summary</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <!-- Items -->
                <div class="space-y-3 max-h-64 overflow-y-auto">
                  <div v-for="item in cartItems" :key="item.product.id" class="flex justify-between text-sm">
                    <div class="flex-1">
                      <p class="font-medium">{{ item.product.name }}</p>
                      <p class="text-gray-600 dark:text-gray-400">Qty: {{ item.quantity }}</p>
                    </div>
                    <p class="font-semibold">
                      ${{ (parseFloat(item.product.price) * item.quantity).toFixed(2) }}
                    </p>
                  </div>
                </div>

                <div class="border-t pt-4">
                  <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span class="text-primary">${{ cartTotal.toFixed(2) }}</span>
                  </div>
                </div>

                <Button
                  @click="submitOrder"
                  :disabled="form.processing || orderPlaced"
                  class="w-full"
                  size="lg"
                >
                  {{ form.processing ? 'Processing...' : 'Place Order' }}
                </Button>

                <p class="text-xs text-center text-gray-600 dark:text-gray-400">
                  By placing your order, you agree to our terms and conditions.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
