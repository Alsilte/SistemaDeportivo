<template>
<div class="notification-system">
    <transition-group name="notification">
    <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="['notification', `notification-${notification.type}`]"
    >
        <div class="notification-content">
        <div class="notification-icon">
            <span v-if="notification.type === 'success'">✅</span>
            <span v-else-if="notification.type === 'error'">❌</span>
            <span v-else-if="notification.type === 'warning'">⚠️</span>
            <span v-else>ℹ️</span>
        </div>
        <div class="notification-text">
            <div v-if="notification.title" class="notification-title">
            {{ notification.title }}
            </div>
            <div class="notification-message">{{ notification.message }}</div>
        </div>
        <button class="notification-close" @click="removeNotification(notification.id)">
            &times;
        </button>
        </div>
    </div>
    </transition-group>
</div>
</template>

<script>
import { ref, onMounted } from 'vue'

export default {
name: 'NotificationSystem',
setup() {
    const notifications = ref([])
    let nextId = 1

    // Add a new notification
    const addNotification = (notification) => {
    const id = nextId++
    notifications.value.push({
        id,
        type: notification.type || 'info',
        title: notification.title || '',
        message: notification.message,
        timeout: notification.timeout || 5000
    })

    // Auto-remove notification after timeout
    setTimeout(() => {
        removeNotification(id)
    }, notification.timeout || 5000)
    }

    // Remove a notification by id
    const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index !== -1) {
        notifications.value.splice(index, 1)
    }
    }

    // Example notification for testing
    onMounted(() => {
    // Uncomment for testing
    // addNotification({
    //   type: 'success',
    //   title: 'Success',
    //   message: 'Operation completed successfully!'
    // })
    })

    return {
    notifications,
    addNotification,
    removeNotification
    }
}
}
</script>

<style scoped>
.notification-system {
position: fixed;
top: 20px;
right: 20px;
z-index: 1000;
width: 350px;
max-width: 100%;
display: flex;
flex-direction: column;
gap: 10px;
}

.notification {
background-color: white;
border-radius: 8px;
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
overflow: hidden;
margin-bottom: 10px;
}

.notification-content {
display: flex;
align-items: flex-start;
padding: 15px;
}

.notification-icon {
margin-right: 12px;
font-size: 1.2rem;
}

.notification-text {
flex: 1;
}

.notification-title {
font-weight: 600;
margin-bottom: 4px;
}

.notification-message {
color: var(--gray-700);
font-size: 0.9rem;
}

.notification-close {
background: none;
border: none;
cursor: pointer;
font-size: 1.25rem;
color: var(--gray-500);
margin-left: 8px;
padding: 0;
line-height: 1;
}

.notification-success {
border-left: 4px solid var(--success-color);
}

.notification-error {
border-left: 4px solid var(--error-color);
}

.notification-warning {
border-left: 4px solid var(--warning-color);
}

.notification-info {
border-left: 4px solid var(--primary-color);
}

/* Animation */
.notification-enter-active,
.notification-leave-active {
transition: all 0.3s ease;
}

.notification-enter-from {
opacity: 0;
transform: translateX(50px);
}

.notification-leave-to {
opacity: 0;
transform: translateX(50px);
}
</style>

