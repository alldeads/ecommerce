<script setup lang="ts">
import { computed } from 'vue'
import { ToastAction, ToastClose, ToastDescription, ToastProvider, ToastRoot, ToastTitle, ToastViewport } from 'radix-vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  open?: boolean
  defaultOpen?: boolean
  class?: string
  variant?: 'default' | 'destructive'
}>()

const emits = defineEmits<{
  'update:open': [value: boolean]
}>()

const handleUpdateOpen = (val: boolean) => {
  emits('update:open', val)
}


const delegatedProps = computed(() => {
  const { class: _, variant, ...delegated } = props
  return delegated
})
</script>

<template>
  <ToastRoot
    v-bind="delegatedProps"
    :class="cn(
      'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
      'bg-background text-foreground',
      'data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full data-[state=open]:sm:slide-in-from-bottom-full',
      variant === 'destructive' && 'destructive group border-destructive bg-destructive text-destructive-foreground',
      props.class,
    )"
    @update:open="handleUpdateOpen"
  >
    <slot />
  </ToastRoot>
</template>
