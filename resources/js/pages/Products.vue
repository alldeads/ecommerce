<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Search, ShoppingCart } from 'lucide-vue-next'
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
  products: Product[]
}

const props = defineProps<Props>()

const { toast } = useToast()

const searchQuery = ref('')
const cart = ref<Record<number, { product: Product; quantity: number }>>({})

// Load cart from localStorage on mount
onMounted(() => {
  const savedCart = localStorage.getItem('cart')
  if (savedCart) {
    cart.value = JSON.parse(savedCart)
  }
})

const filteredProducts = computed(() => {
  if (!searchQuery.value) {
    return props.products
  }
  const query = searchQuery.value.toLowerCase()
  return props.products.filter((product: Product) =>
    product.name.toLowerCase().includes(query) ||
    product.description.toLowerCase().includes(query)
  )
})

const cartItemsCount = computed(() => {
  return (Object.values(cart.value) as { product: Product; quantity: number }[]).reduce((sum: number, item) => sum + item.quantity, 0)
})

const saveCart = () => {
  localStorage.setItem('cart', JSON.stringify(cart.value))
}

const addToCart = (product: Product) => {
  if (cart.value[product.id]) {
    cart.value[product.id].quantity++
  } else {
    cart.value[product.id] = { product, quantity: 1 }
  }
  saveCart()

  toast({
    title: 'Added to cart',
    description: `${product.name} has been added to your cart.`,
  })
}

const viewProduct = (productId: number) => {
  router.visit(`/products/${productId}`)
}

const viewCart = () => {
  router.visit('/cart')
}
</script>

<template>
  <AppLayout>
    <Head title="Product Catalog" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
              Product Catalog
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Browse our selection of {{ props.products.length }} products
            </p>
          </div>

          <Button
            v-if="cartItemsCount > 0"
            @click="viewCart"
            variant="default"
            class="flex items-center gap-2"
          >
            <ShoppingCart class="h-4 w-4" />
            Cart ({{ cartItemsCount }})
          </Button>
        </div>

        <!-- Search -->
        <div class="mb-8">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="searchQuery"
              type="text"
              placeholder="Search products..."
              class="pl-10"
            />
          </div>
        </div>

        <!-- Products Grid -->
        <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <Card
            v-for="product in filteredProducts"
            :key="product.id"
            class="cursor-pointer hover:shadow-lg transition-shadow"
          >
            <CardHeader class="p-0">
              <div
                class="h-48 bg-gray-200 dark:bg-gray-700 rounded-t-lg overflow-hidden"
                @click="viewProduct(product.id)"
              >
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                  No Image
                </div>
              </div>
            </CardHeader>

            <CardContent class="p-4" @click="viewProduct(product.id)">
              <CardTitle class="text-lg mb-2">{{ product.name }}</CardTitle>
              <CardDescription class="line-clamp-2 mb-3">
                {{ product.description }}
              </CardDescription>

              <div class="flex items-center justify-between">
                <span class="text-2xl font-bold text-primary">
                  ${{ parseFloat(product.price).toFixed(2) }}
                </span>
                <Badge v-if="product.stock > 0" variant="secondary">
                  {{ product.stock }} in stock
                </Badge>
                <Badge v-else variant="destructive">
                  Out of stock
                </Badge>
              </div>
            </CardContent>

            <CardFooter class="p-4 pt-0">
              <Button
                @click.stop="addToCart(product)"
                :disabled="product.stock === 0"
                class="w-full"
              >
                <ShoppingCart class="h-4 w-4 mr-2" />
                Add to Cart
              </Button>
            </CardFooter>
          </Card>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400 text-lg">
            No products found matching "{{ searchQuery }}"
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
