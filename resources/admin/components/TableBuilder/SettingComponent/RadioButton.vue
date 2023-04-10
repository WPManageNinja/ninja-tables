<template>
    <div class="radioButton">
        <span>{{ label }}</span><br>
        <el-radio-group :value="value" size="small" @input="$emit('update', $event)">
            <el-radio-button v-for="option in options" :key="option.value" :label="option.label"></el-radio-button>
        </el-radio-group>
    </div>
</template>

<script>
export default {
    name: "RadioButton",
    model: {
        prop: "value",
        event: "update"
    },
    props: {
        value: {
            type: String,
            default: 'M'
        },
        options: {
            type: Array,
            default: () => []
        },
        event: {
            type: String,
            default: 'update'
        },
        label: {
            type: String,
            default: ""
        }
    }
}
</script>

<style scoped>
.radioButton {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-right: 10px;
}
</style>