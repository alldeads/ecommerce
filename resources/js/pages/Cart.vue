<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Trash2, Plus, Minus, ShoppingBag } from 'lucide-vue-next'

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

const cart = ref<Record<number, CartItem>>({})

onMounted(() => {
  const savedCart = localStorage.getItem('cart')
  if (savedCart) {
    cart.value = JSON.parse(savedCart)
  }
})

const cartItems = computed(() => Object.values(cart.value))

const cartTotal = computed(() => {
  return cartItems.value.reduce((sum: number, item: CartItem) => {
    return sum + (parseFloat(item.product.price) * item.quantity)
  }, 0)
})

const cartItemsCount = computed(() => {
  return cartItems.value.reduce((sum: number, item: CartItem) => sum + item.quantity, 0)
})

const updateQuantity = (productId: number, newQuantity: number) => {
  if (newQuantity <= 0) {
    removeItem(productId)
    return
  }

  const item = cart.value[productId]
  if (item && newQuantity <= item.product.stock) {
    item.quantity = newQuantity
    saveCart()
  }
}

const increaseQuantity = (productId: number) => {
  const item = cart.value[productId]
  if (item && item.quantity < item.product.stock) {
    item.quantity++
    saveCart()
  }
}

const decreaseQuantity = (productId: number) => {
  const item = cart.value[productId]
  if (item && item.quantity > 1) {
    item.quantity--
    saveCart()
  }
}

const removeItem = (productId: number) => {
  delete cart.value[productId]
  saveCart()
}

const saveCart = () => {
  localStorage.setItem('cart', JSON.stringify(cart.value))
}

const clearCart = () => {
  cart.value = {}
  localStorage.removeItem('cart')
}

const continueShopping = () => {
  router.visit('/products')
}

const proceedToCheckout = () => {
  router.visit('/checkout')
}
</script>

<template>
  <AppLayout>
    <Head title="Shopping Cart" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">
          Shopping Cart
        </h1>

        <!-- Empty Cart -->
        <div v-if="cartItems.length === 0" class="text-center py-12">
          <ShoppingBag class="h-24 w-24 mx-auto text-gray-400 mb-4" />
          <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            Your cart is empty
          </h2>
          <p class="text-gray-600 dark:text-gray-400 mb-6">
            Add some products to get started!
          </p>
          <Button @click="continueShopping">
            Continue Shopping
          </Button>
        </div>

        <!-- Cart Items -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Items List -->
          <div class="lg:col-span-2 space-y-4">
            <Card v-for="item in cartItems" :key="item.product.id">
              <CardContent class="p-6">
                <div class="flex gap-4">
                  <!-- Product Image -->
                  <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden flex-shrink-0">
                    <img
                      v-if="item.product.image_url"
                      :src="item.product.image_url"
                      :alt="item.product.name"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                      No Image
                    </div>
                  </div>

                  <!-- Product Details -->
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold mb-1">{{ item.product.name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                      SKU: {{ item.product.sku }}
                    </p>
                    <p class="text-xl font-bold text-primary">
                      ${{ parseFloat(item.product.price).toFixed(2) }}
                    </p>
                  </div>

                  <!-- Quantity Controls -->
                  <div class="flex flex-col items-end gap-2">
                    <Button
                      @click="removeItem(item.product.id)"
                      variant="ghost"
                      size="sm"
                      class="text-red-500 hover:text-red-700"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>

                    <div class="flex items-center border rounded-md">
                      <Button
                        @click="decreaseQuantity(item.product.id)"
                        variant="ghost"
                        size="sm"
                        :disabled="item.quantity <= 1"
                        class="h-8 w-8"
                      >
                        <Minus class="h-3 w-3" />
                      </Button>
                      <Input
                        :model-value="item.quantity"
                        @update:model-value="(val) => updateQuantity(item.product.id, Number(val))"
                        type="number"
                        :min="1"
                        :max="item.product.stock"
                        class="w-16 text-center border-0 focus-visible:ring-0 h-8"
                      />
                      <Button
                        @click="increaseQuantity(item.product.id)"
                        variant="ghost"
                        size="sm"
                        :disabled="item.quantity >= item.product.stock"
                        class="h-8 w-8"
                      >
                        <Plus class="h-3 w-3" />
                      </Button>
                    </div>

                    <p class="text-sm font-semibold">
                      Subtotal: ${{ (parseFloat(item.product.price) * item.quantity).toFixed(2) }}
                    </p>
                  </div>
                </div>
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
                <div class="flex justify-between text-sm">
                  <span>Items ({{ cartItemsCount }})</span>
                  <span>${{ cartTotal.toFixed(2) }}</span>
                </div>

                <div class="border-t pt-4">
                  <div class="flex justify-between text-lg font-bold">
                    <span>Total</span>
                    <span class="text-primary">${{ cartTotal.toFixed(2) }}</span>
                  </div>
                </div>

                <Button @click="proceedToCheckout" class="w-full" size="lg">
                  Proceed to Checkout
                </Button>

                <Button @click="continueShopping" variant="outline" class="w-full">
                  Continue Shopping
                </Button>

                <Button @click="clearCart" variant="ghost" class="w-full text-red-500 hover:text-red-700">
                  Clear Cart
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
