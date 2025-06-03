import { reactive } from 'vue';

const bus = reactive({
    events: {},
    emit(event, ...args) {
        if (this.events[event]) {
            this.events[event].forEach(callback => callback(...args))
        }
    },
    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = []
        }
        this.events[event].push(callback)
        
        // Return unsubscribe function
        return () => this.off(event, callback)
    },
    off(event, callback) {
        if (this.events[event]) {
            this.events[event] = this.events[event].filter(cb => cb !== callback)
        }
    }
})

// Create a composable for Vue components
export function useEventBus() {
    return bus
}