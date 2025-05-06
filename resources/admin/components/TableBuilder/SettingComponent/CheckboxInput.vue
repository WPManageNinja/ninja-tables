<template>
    <div class="block nt-checkbox-group">
        <span>{{ label }}</span>
        <el-checkbox-group
            size="small"
            :value="value"
            @input="$emit('update', $event)">

            <el-checkbox
                v-for="option in options"
                :key="option.value"
                :label="option.value"
                :value="option.value"
            />

        </el-checkbox-group>
    </div>
</template>
<script>
export default {
    name: "CheckboxInput",
    
    model: {
        prop: "value",
        event: "update"
    },
    props: {
        value: {
            type: Array,
            default: () => []
        },
        label: {
            type: String,
            default: ""
        },
        options: {
            type: Array,
            default: () => []
        }
    }
};
</script>

<style lang="scss">
.nt-checkbox-group {
    .el-checkbox-group {
        display: grid;
    }
}

</style>
