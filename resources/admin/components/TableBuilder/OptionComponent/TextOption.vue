<template>
    <div class="component-wrapper">
        <el-input
            type="textarea"
            :autosize="{ minRows: 3 }"
            :placeholder="$t('Enter here')"
            v-model="item.data.value"
            style="width: 96%; margin-bottom: 3px;"
        >
        </el-input>
        <color-input :label="$t('Font color')" v-model="item.data.style.color"></color-input>
        <slider-input :label="$t('Font Size')" v-model="item.data.style.fontSize" :min="10" :max="45"
                      :step="1"></slider-input>
        <alignment :label="$t('Alignment')" v-model="item.data.style.alignment"></alignment>
        <checkbox :label="$t('Font Style')" v-model="item.data.style.fontWeight" :options="fontStyleOptions"></checkbox>
    </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import SliderInput from "../SettingComponent/SliderInput";
import Alignment from "../SettingComponent/Alignment";
import Checkbox from '../SettingComponent/CheckboxInput'

export default {
    
    name: "TextOption",
    data() {
        return {
            fontStyleOptions: [
                {label: 'Bold', value: 'bold'},
                {label: 'Italic', value: 'italic'},
                {label: 'Underline', value: 'underline'},
            ]
        };
    },
    props: ["item"],
    components: {
        ColorInput,
        SliderInput,
        Alignment,
        Checkbox
    }
}
</script>

