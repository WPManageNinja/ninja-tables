import { reactive, readonly } from 'vue'

// Create a reactive state object
const state = reactive({
  config: null,
  table: {},
  preview_url: '',
  tableId: null
})

// Methods to modify the state
const actions = {
  setConfig(configData) {
    if (configData) {
      state.config = configData
      state.table = configData.table || {}
      state.preview_url = configData.preview_url || '#'
    }
  },
  
  setTableId(id) {
    state.tableId = id
  },
  
  updateColumns(columns) {
    if (state.config) {
      state.config.columns = columns
    }
  },
  
  updateTable(tableData) {
    if (state.config) {
      state.config.table = tableData
      state.table = tableData
    }
  },
  
  // Add any other update methods you need
  updateSettings(settings) {
    if (state.config) {
      
      state.config.settings = settings
    }
  }
}

// Export readonly state and actions
export default {
  state,
  ...actions
}