<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ArrowLeft, ShoppingCart, Plus, Minus } from 'lucide-vue-next'
import { useToast } from '@/components/ui/toast'

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

interface Props {
  product: Product
}

const props = defineProps<Props>()

const { toast } = useToast()

const quantity = ref(1)

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const increaseQuantity = () => {
  if (quantity.value < props.product.stock) {
    quantity.value++
  }
}

const addToCart = () => {
  // Store in localStorage for cart
  const cart = JSON.parse(localStorage.getItem('cart') || '{}')

  if (cart[props.product.id]) {
    cart[props.product.id].quantity += quantity.value
  } else {
    cart[props.product.id] = {
      product: props.product,
      quantity: quantity.value
    }
  }

  localStorage.setItem('cart', JSON.stringify(cart))

  toast({
    title: 'Added to cart',
    description: `${quantity.value} ${props.product.name} added to your cart.`,
  })

  router.visit('/cart')
}

const goBack = () => {
  router.visit('/products')
}
</script>

<template>
  <AppLayout>
    <Head :title="product.name" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <Button
          @click="goBack"
          variant="ghost"
          class="mb-6 flex items-center gap-2"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to Products
        </Button>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Product Image -->
          <div class="bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden aspect-square">
            <img
              v-if="product.image_url"
              :src="product.image_url"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xl">
              No Image Available
            </div>
          </div>

          <!-- Product Details -->
          <div>
            <Card>
              <CardHeader>
                <div class="flex items-start justify-between">
                  <div>
                    <CardTitle class="text-3xl mb-2">{{ product.name }}</CardTitle>
                    <CardDescription class="text-sm">SKU: {{ product.sku }}</CardDescription>
                  </div>
                  <Badge v-if="product.stock > 0" variant="secondary" class="text-sm">
                    {{ product.stock }} in stock
                  </Badge>
                  <Badge v-else variant="destructive" class="text-sm">
                    Out of stock
                  </Badge>
                </div>
              </CardHeader>

              <CardContent class="space-y-6">
                <!-- Price -->
                <div>
                  <span class="text-4xl font-bold text-primary">
                    ${{ parseFloat(product.price).toFixed(2) }}
                  </span>
                </div>

                <!-- Description -->
                <div>
                  <h3 class="text-lg font-semibold mb-2">Description</h3>
                  <p class="text-gray-600 dark:text-gray-400">
                    {{ product.description }}
                  </p>
                </div>

                <!-- Quantity Selector -->
                <div v-if="product.stock > 0">
                  <Label class="text-lg font-semibold mb-2 block">Quantity</Label>
                  <div class="flex items-center gap-4">
                    <div class="flex items-center border rounded-md">
                      <Button
                        @click="decreaseQuantity"
                        variant="ghost"
                        size="sm"
                        :disabled="quantity <= 1"
                        class="h-10 w-10"
                      >
                        <Minus class="h-4 w-4" />
                      </Button>
                      <Input
                        v-model.number="quantity"
                        type="number"
                        :min="1"
                        :max="product.stock"
                        class="w-20 text-center border-0 focus-visible:ring-0"
                      />
                      <Button
                        @click="increaseQuantity"
                        variant="ghost"
                        size="sm"
                        :disabled="quantity >= product.stock"
                        class="h-10 w-10"
                      >
                        <Plus class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- Add to Cart Button -->
                <Button
                  @click="addToCart"
                  :disabled="product.stock === 0"
                  size="lg"
                  class="w-full"
                >
                  <ShoppingCart class="h-5 w-5 mr-2" />
                  Add to Cart
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
