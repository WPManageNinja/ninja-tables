import { reactive } from 'vue'

export function createEventBus() {
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
        },
        off(event, callback) {
            if (this.events[event]) {
                this.events[event] = this.events[event].filter(cb => cb !== callback)
            }
        }
    })
    return bus
}