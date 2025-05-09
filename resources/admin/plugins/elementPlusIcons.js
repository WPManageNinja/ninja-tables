import {
    Edit,
    Delete,
    Search,
    Plus,
    ArrowDown,
    UploadFilled,
    Download,
    Document,
    InfoFilled,
    Setting,
    Loading,
    Star,
    SetUp,
    Menu
} from '@element-plus/icons-vue'

const icons = {
    Edit,
    Delete,
    Search,
    Plus,
    ArrowDown,
    UploadFilled,
    Download,
    Document,
    InfoFilled,
    Setting,
    Loading,
    Star,
    SetUp,
    Menu
}

export default {
    install(app) {
        for (const [name, component] of Object.entries(icons)) {
            app.component(name, component)
        }
    }
}
