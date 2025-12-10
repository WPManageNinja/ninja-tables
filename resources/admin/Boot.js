import NINJATABLE from './NINJATABLES'

// Error handling
const oldOnError = window.onerror
window.onerror = function() {
    if(oldOnError) oldOnError.apply(this, arguments)
    return true
}

// Initialize NINJATABLE and make it globally available
window.NINJATABLE = new NINJATABLE()

// Export for use in other files
export default window.NINJATABLE
