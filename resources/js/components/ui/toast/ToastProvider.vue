<script setup lang="ts">
import { ToastProvider } from 'radix-vue'

defineProps<{
  label?: string
  duration?: number
  swipeDirection?: 'up' | 'down' | 'left' | 'right'
  swipeThreshold?: number
}>()
</script>

<template>
  <ToastProvider v-bind="$props">
    <slot />
  </ToastProvider>
</template>
