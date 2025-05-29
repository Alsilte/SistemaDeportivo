<template>
    <div class="fixed top-4 right-4 z-50 space-y-4 max-w-sm w-full">
      <TransitionGroup
        name="notification"
        tag="div"
        class="space-y-4"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'rounded-lg shadow-lg border p-4 backdrop-blur-sm',
            notificationClasses(notification.type)
          ]"
        >
          <div class="flex items-start">
            <!-- Icon -->
            <div class="flex-shrink-0 mr-3">
              <CheckCircleIcon 
                v-if="notification.type === 'success'" 
                class="w-6 h-6 text-success-600" 
              />
              <XCircleIcon 
                v-else-if="notification.type === 'error'" 
                class="w-6 h-6 text-danger-600" 
              />
              <ExclamationTriangleIcon 
                v-else-if="notification.type === 'warning'" 
                class="w-6 h-6 text-warning-600" 
              />
              <InformationCircleIcon 
                v-else 
                class="w-6 h-6 text-primary-600" 
              />
            </div>
  
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <h4 
                v-if="notification.title" 
                :class="[
                  'text-sm font-medium mb-1',
                  titleClasses(notification.type)
                ]"
              >
                {{ notification.title }}
              </h4>
              <p 
                :class="[
                  'text-sm',
                  messageClasses(notification.type)
                ]"
              >
                {{ notification.message }}
              </p>
              
              <!-- Action Button -->
              <div v-if="notification.action" class="mt-3">
                <button
                  @click="handleAction(notification)"
                  :class="[
                    'text-sm font-medium underline hover:no-underline transition-all',
                    actionClasses(notification.type)
                  ]"
                >
                  {{ notification.action.text }}
                </button>
              </div>
            </div>
  
            <!-- Close Button -->
            <div class="flex-shrink-0 ml-3">
              <button
                @click="removeNotification(notification.id)"
                :class="[
                  'rounded-lg p-1 transition-colors',
                  closeButtonClasses(notification.type)
                ]"
              >
                <XMarkIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
  
          <!-- Progress Bar (for auto-dismiss) -->
          <div 
            v-if="notification.duration && notification.duration > 0"
            class="mt-3 h-1 bg-gray-200 rounded-full overflow-hidden"
          >
            <div
              class="h-full bg-current transition-all linear"
              :style="{ 
                width: getProgressWidth(notification),
                animationDuration: `${notification.duration}ms`
              }"
            ></div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </template>
  
  <script>
  /**
   * Notification System Component
   * Global notification/toast system for user feedback
   */
  
  import { ref, onMounted } from 'vue'
  import {
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon
  } from '@heroicons/vue/24/outline'
  
  export default {
    name: 'NotificationSystem',
    components: {
      CheckCircleIcon,
      XCircleIcon,
      ExclamationTriangleIcon,
      InformationCircleIcon,
      XMarkIcon
    },
    setup() {
      const notifications = ref([])
  
      // Default duration for auto-dismiss (5 seconds)
      const DEFAULT_DURATION = 5000
  
      /**
       * Add notification to the system
       */
      const addNotification = (notification) => {
        const id = Date.now() + Math.random()
        const newNotification = {
          id,
          type: notification.type || 'info',
          title: notification.title,
          message: notification.message,
          duration: notification.duration !== undefined ? notification.duration : DEFAULT_DURATION,
          action: notification.action,
          createdAt: Date.now()
        }
  
        notifications.value.push(newNotification)
  
        // Auto-dismiss if duration is set
        if (newNotification.duration > 0) {
          setTimeout(() => {
            removeNotification(id)
          }, newNotification.duration)
        }
  
        return id
      }
  
      /**
       * Remove notification by ID
       */
      const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id)
        if (index > -1) {
          notifications.value.splice(index, 1)
        }
      }
  
      /**
       * Clear all notifications
       */
      const clearAll = () => {
        notifications.value = []
      }
  
      /**
       * Handle notification action click
       */
      const handleAction = (notification) => {
        if (notification.action?.callback) {
          notification.action.callback()
        }
        removeNotification(notification.id)
      }
  
      /**
       * Get progress bar width for auto-dismiss
       */
      const getProgressWidth = (notification) => {
        if (!notification.duration || notification.duration <= 0) return '0%'
        
        const elapsed = Date.now() - notification.createdAt
        const remaining = Math.max(0, notification.duration - elapsed)
        const percentage = (remaining / notification.duration) * 100
        
        return `${percentage}%`
      }
  
      /**
       * CSS classes based on notification type
       */
      const notificationClasses = (type) => {
        const classes = {
          success: 'bg-success-50 border-success-200',
          error: 'bg-danger-50 border-danger-200',
          warning: 'bg-warning-50 border-warning-200',
          info: 'bg-primary-50 border-primary-200'
        }
        return classes[type] || classes.info
      }
  
      const titleClasses = (type) => {
        const classes = {
          success: 'text-success-800',
          error: 'text-danger-800',
          warning: 'text-warning-800', 
          info: 'text-primary-800'
        }
        return classes[type] || classes.info
      }
  
      const messageClasses = (type) => {
        const classes = {
          success: 'text-success-700',
          error: 'text-danger-700',
          warning: 'text-warning-700',
          info: 'text-primary-700'
        }
        return classes[type] || classes.info
      }
  
      const actionClasses = (type) => {
        const classes = {
          success: 'text-success-700 hover:text-success-800',
          error: 'text-danger-700 hover:text-danger-800',
          warning: 'text-warning-700 hover:text-warning-800',
          info: 'text-primary-700 hover:text-primary-800'
        }
        return classes[type] || classes.info
      }
  
      const closeButtonClasses = (type) => {
        const classes = {
          success: 'text-success-600 hover:bg-success-100',
          error: 'text-danger-600 hover:bg-danger-100',
          warning: 'text-warning-600 hover:bg-warning-100',
          info: 'text-primary-600 hover:bg-primary-100'
        }
        return classes[type] || classes.info
      }
  
      // Expose to global scope for use in other components
      onMounted(() => {
        // Make notification system globally available
        window.$notify = {
          success: (message, options = {}) => addNotification({ ...options, type: 'success', message }),
          error: (message, options = {}) => addNotification({ ...options, type: 'error', message }),
          warning: (message, options = {}) => addNotification({ ...options, type: 'warning', message }),
          info: (message, options = {}) => addNotification({ ...options, type: 'info', message }),
          clear: clearAll
        }
      })
  
      return {
        notifications,
        addNotification,
        removeNotification,
        clearAll,
        handleAction,
        getProgressWidth,
        notificationClasses,
        titleClasses,
        messageClasses,
        actionClasses,
        closeButtonClasses
      }
    }
  }
  </script>
  
  <style scoped>
  /* Notification transitions */
  .notification-enter-active {
    transition: all 0.3s ease-out;
  }
  
  .notification-leave-active {
    transition: all 0.3s ease-in;
  }
  
  .notification-enter-from {
    transform: translateX(100%);
    opacity: 0;
  }
  
  .notification-leave-to {
    transform: translateX(100%);
    opacity: 0;
  }
  
  .notification-move {
    transition: transform 0.3s ease;
  }
  </style>