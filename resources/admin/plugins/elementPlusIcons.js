import {
    Edit,
    Delete,
    Search,
    Plus,
    ArrowDown
} from '@element-plus/icons-vue'

const icons = {
    Edit,
    Delete,
    Search,
    Plus,
    ArrowDown
}

export default {
    install(app) {
        for (const [name, component] of Object.entries(icons)) {
            app.component(name, component)
        }
    }
}
