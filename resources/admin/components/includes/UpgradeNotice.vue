<template>
    <el-alert title=""
              type="warning"
              :closable="false"
              show-icon
              class="update-notice"
    >
        <p>
            Please update Ninja Tables Pro Plugin to latest version. Required Ninja Table Version: 3.0 or later
        </p>
    </el-alert>
</template>

<script>
    export default {
        name: "UpgradeNotice"
    }
</script>
