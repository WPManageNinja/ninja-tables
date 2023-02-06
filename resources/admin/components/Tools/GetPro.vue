<template>
  <a v-if="!has_pro" href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade"
     target="_blank">
    <el-button :type="type" :size="size">{{ $t(text) }}</el-button>
  </a>
</template>

<script>
  export default {
    name: 'GetPro',
    data() {
      return {
        has_pro: window.ninja_table_admin.hasPro
      }
    },
    props: {
      text: {
        type: String,
        default: 'Get Pro'
      },
      type: {
        type: String,
        default: 'danger'
      },
      size: {
        type: String,
        default: 'mini'
      }
    }
  }
</script>
