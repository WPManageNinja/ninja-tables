import './Boot.js'
import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import Clipboard from "clipboard"
import { routes } from './routes'
import Application from './App.vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css';
import './css/ninja-tables-admin.scss';

// Initialize variables in wider scope
let app
let router

try {
    // Create router
    router = createRouter({
        history: createWebHashHistory(),
        routes: window.NINJATABLE.applyFilters('ninja_table_global_routes', routes),
        linkActiveClass: 'active'
    })
    // Create Vue app
    app = createApp(Application)

    // Use Element Plus
    app.use(ElementPlus)

    // Global properties and methods
    app.config.globalProperties.$t = (str) => {
        let transString = ninja_table_admin.i18n[str]
        return transString || str
    }

    app.config.globalProperties.setStoreData = (key, value) => {
        if (window.localStorage) {
            localStorage.setItem("ninjatable_" + key, value)
        }
    }

    app.config.globalProperties.getFromStore = (key, defaultValue) => {
        if (window.localStorage) {
            let itemValue = localStorage.getItem('ninjatable_' + key)
            if (itemValue) {
                return itemValue
            }
        }
        return defaultValue
    }

    // NINJATABLE methods
    const ninjaMethods = {
        applyFilters: window.NINJATABLE.applyFilters,
        addFilter: window.NINJATABLE.addFilter,
        addAction: window.NINJATABLE.addAction, // Fixed: was using addFilter
        doAction: window.NINJATABLE.doAction,
        $get: window.NINJATABLE.$get,
        $post: window.NINJATABLE.$post,
        $del: window.NINJATABLE.$del,
        $put: window.NINJATABLE.$put,
        $patch: window.NINJATABLE.$patch,
        $getJSON: window.NINJATABLE.$getJSON,
    }

    // Add NINJATABLE methods to global properties
    Object.entries(ninjaMethods).forEach(([key, value]) => {
        app.config.globalProperties[key] = value
    })

    // Clipboard functionality with error handling
    app.config.globalProperties.clipboard = function () {
        if (!window.clipboard) {
            window.clipboard = new Clipboard('.copy')
            window.clipboard.on('success', () => {
                this.$message({
                    message: 'Copied to Clipboard!',
                    type: 'success'
                })
            })
            window.clipboard.on('error', (e) => {
                console.error('Clipboard error:', e)
            })
        }
    }

    // Directives
    app.directive('focus', {
        mounted: (el) => el.focus()
    })

    // Use router
    app.use(router)

    // Error handler
    app.config.errorHandler = (err, vm, info) => {
        console.error('Vue Error:', err)
        console.error('Error Info:', info)
    }
    
    window.ninjaApp = app.mount('#data-tables-app')

} catch (error) {
    console.error('Initialization error:', error)
}

// Export for use in other files
export { app, router }
