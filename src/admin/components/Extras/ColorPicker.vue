<template>
    <div class="form_group">
        <el-color-picker
                show-alpha
                @active-change="changeColor"
                :value="value"
                :disabled="disabled"
                @input="$emit('update', $event)"
        ></el-color-picker>
        <label v-if="label">{{label}}</label>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'ninja_color_picker',
        props: ['label', 'value', 'disabled'],
        model: {
          prop: 'value',
          event: 'update'
        },
        methods: {
            changeColor(color) {
              this.$emit('update', color)
            }
        }
    }
</script>

