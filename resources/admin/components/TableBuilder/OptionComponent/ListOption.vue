<template>
    <div>
        <el-tabs v-model="activeName">
            <el-tab-pane class="component-wrapper" :label="$t('General')" name="general">
                <slider
                    v-model="item.data.style.lineHeight"
                    :label="$t('Item Spacing Vertically')"
                    :max="50"
                    :min="globalFontValue"
                    :step="1"
                ></slider>
                <slider
                    v-model="item.data.style.itemSpacing"
                    :label="$t('Space Between Icon and Text')"
                    :max="100"
                    :min="-5"
                    :step="1"
                ></slider>
                <alignment :label="$t('Alignment')" v-model="item.data.style.alignment"></alignment>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('Text')" name="text">
                <color-input
                    :label="$t('Font color')"
                    v-model="item.data.style.color"
                ></color-input>
                <slider
                    v-model="item.data.style.fontSize"
                    :label="$t('Font Size')"
                    :max="50"
                    :min="10"
                    :step="1"
                ></slider>
                <checkbox
                    :label="$t('Font Style')"
                    :options="fontStyleOptions"
                    v-model="item.data.style.fontWeight">
                </checkbox>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('List')" name="list">
                <div>
                    <p>{{ $t('List Type') }}</p>
                    <el-radio-group v-model="item.data.style.listType">
                        <el-radio label="ul">{{ $t('Unordered List') }}</el-radio>
                        <el-radio label="ol">{{ $t('Ordered List') }}</el-radio>
                    </el-radio-group>
                </div>
                <div>
                    <p>{{ $t('List Icon') }}</p>
                    <el-select
                        size="small"
                        v-model="item.data.style.listStyle"
                        :placeholder="$t('List Icon')">
                        <el-option
                            v-for="item in updatedList"
                            :key="item"
                            :label="item"
                            :value="item">
                        </el-option>
                    </el-select>
                </div>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import Slider from "../SettingComponent/SliderInput";
import ColorInput from "../SettingComponent/ColorInput";
import Alignment from "../SettingComponent/Alignment";
import Checkbox from "../SettingComponent/CheckboxInput";

export default {
    name: "ListOption",
    props: ["item", "settings"],
    data() {
        return {
            activeName: 'general',
            list: {
                ul: ['circle', 'square', 'disc', 'none'],
                ol: ['upper-roman', 'lower-roman', 'upper-alpha', 'lower-alpha', 'decimal']
            },
            fontStyleOptions: [
                {label: 'Bold', value: 'bold'},
                {label: 'Italic', value: 'italic'},
                {label: 'Underline', value: 'underline'},
            ]
        }
    },
    components: {
        ColorInput,
        Slider,
        Alignment,
        Checkbox
    },
    computed: {
        updatedList() {
            this.item.data.style.listStyle = this.item.data.style.listType === 'ul' ? 'circle' : 'upper-roman';
            return this.list[this.item.data.style.listType];
        },
        globalFontValue() {
            return Number(this.settings.global_styling.options.font_size.value ) - Number(2)
        },
    }
};
</script>
