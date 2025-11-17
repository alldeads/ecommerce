import { ref } from 'vue'

export type ToastVariant = 'default' | 'destructive'

export interface Toast {
  id: string
  title?: string
  description?: string
  variant?: ToastVariant
  open?: boolean
}

const toasts = ref<Toast[]>([])

export function useToast() {
  const toast = ({ title, description, variant = 'default', duration = 3000 }: Omit<Toast, 'id' | 'open'> & { duration?: number }) => {
    const id = Math.random().toString(36).substring(2, 9)

    const newToast: Toast = {
      id,
      title,
      description,
      variant,
      open: true,
    }

    toasts.value.push(newToast)

    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id)
    }, duration)

    return id
  }

  return {
    toast,
    toasts,
  }
}
