import {
    ElButton,
    ElTable,
    ElTableColumn,
    ElDialog,
    ElPopover,
    ElLoading,
    ElMessage,
    ElMessageBox,
    ElIcon,
    ElTooltip,
    ElPagination,
    ElCollapse,
    ElCollapseItem,
    ElContainer,
    ElAside,
    ElMain,
    ElMenu,
    ElMenuItem,
    ElHeader,
    ElColorPicker,
    ElForm,
    ElFormItem,
    ElInput,
    ElCheckbox,
    ElRadioGroup,
    ElRadio,
    ElSelect,
    ElOption,
    ElOptionGroup,
    ElSwitch,
    ElCheckboxGroup,
    ElRadioButton,
    ElTabPane,
    ElTabs,
    ElSteps,
    ElStep,
    ElAlert,
    ElRow,
    ElCol,
    ElTransfer,
    ElUpload,
    ElDatePicker,
    // Table builder components
    ElInputNumber,
    ElSlider,
    ElCard,
    ElRate,
    ElProgress,
    ElButtonGroup,
    ElDropdown,
    ElDropdownMenu,
    ElDropdownItem,
    ElAvatar,
    ElBadge,
    ElTag
} from 'element-plus'
import 'element-plus/dist/index.css'

// Create a plugin to install all Element Plus components
const ElementPlus = {
    install(app) {
        // Register components
        app.use(ElButton)
        app.use(ElUpload)
        app.use(ElDatePicker)
        app.use(ElTable)
        app.use(ElColorPicker)
        app.use(ElPagination)
        app.use(ElTableColumn)
        app.use(ElPopover)
        app.use(ElMenu)
        app.use(ElHeader)
        app.use(ElMenuItem)
        app.use(ElLoading)
        app.use(ElIcon)
        app.use(ElTooltip)
        app.use(ElContainer)
        app.use(ElAside)
        app.use(ElMain)
        app.use(ElCollapse)
        app.use(ElCollapseItem)
        app.use(ElDialog)
        app.use(ElForm)
        app.use(ElFormItem)
        app.use(ElInput)
        app.use(ElSelect)
        app.use(ElOption)
        app.use(ElOptionGroup)
        app.use(ElCheckbox)
        app.use(ElRadioGroup)
        app.use(ElRadio)
        app.use(ElRadioButton)
        app.use(ElSwitch)
        app.use(ElCheckboxGroup)
        app.use(ElTabs)
        app.use(ElTabPane)
        app.use(ElSteps)
        app.use(ElStep)
        app.use(ElAlert)
        app.use(ElRow)
        app.use(ElCol)
        app.use(ElTransfer)
        app.use(ElSlider)
        app.use(ElCard)
        app.use(ElInputNumber)
        app.use(ElRate)
        app.use(ElProgress)
        app.use(ElButtonGroup)
        app.use(ElDropdown)
        app.use(ElDropdownMenu)
        app.use(ElDropdownItem)
        app.use(ElAvatar)
        app.use(ElBadge)
        app.use(ElTag)

        // Register global methods
        app.config.globalProperties.$message = ElMessage
        app.config.globalProperties.$msgbox = ElMessageBox
        app.config.globalProperties.$alert = ElMessageBox.alert
        app.config.globalProperties.$confirm = ElMessageBox.confirm
        app.config.globalProperties.$prompt = ElMessageBox.prompt
    }
}

export default ElementPlus