import { ref } from 'vue'

const bus = ref(new Map())

export function useEventBus() {
    function on(event, callback) {
        if (!bus.value.has(event)) {
            bus.value.set(event, [])
        }
        bus.value.get(event).push(callback)
    }

    function off(event, callback) {
        if (this.events[event]) {
            this.events[event] = this.events[event].filter(cb => cb !== callback)
        }
    }

    function emit(event, data) {
        if (bus.value.has(event)) {
            bus.value.get(event).forEach(callback => callback(data))
        }
    }

    return { on, emit, off }
}