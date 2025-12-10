<template>
    <div>
        <el-tabs v-model="activeName">
            <el-tab-pane class="component-wrapper" :label="$t('General')" name="general">
                <span contenteditable="true" @input="updateText" :placeholder="$t('Enter here')" v-html="item.data.value"
                    style="width: 90%; display: block; margin-bottom: 3px; border: 1px solid lightgray; border-radius: 5px; padding: 9px 12px;">
                </span>
                <slider v-model="item.data.style.itemSpacing" :label="$t('Item Spacing')" :max="200" :min="0" :step="1">
                </slider>
                <alignment :label="$t('Alignment')" v-model="item.data.style.alignment"></alignment>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('Text')" name="text">
                <color-input :label="$t('Font color')" v-model="item.data.style.color"></color-input>
                <slider v-model="fontSizeValue" :label="$t('Font Size')" :max="50" :min="10" :step="1">
                </slider>
                <checkbox :label="$t('Font Style')" :options="fontStyleOptions" v-model="item.data.style.fontWeight">
                </checkbox>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('Icon')" name="icon">
                <color-input v-if="extension" :label="$t('Color')" v-model="item.data.style.iconColor"></color-input>
                <slider :label="$t('Icon Size')" :max="50" :min="10" :step="1" v-model="item.data.style.iconFontSize">
                </slider>
                <icon :item="item" setValue="iconName"></icon>
                <div>
                    <span>{{ $t('Icon Position') }}</span><br>
                    <el-select v-model="item.data.style.iconPosition" placeholder="Select" size="small">
                        <el-option v-for="item in ['left', 'right']" :key="item" :label="item" :value="item">
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
import Icon from "./SplitComponent/Icon";
import Checkbox from "../SettingComponent/CheckboxInput";
import { restoreCursorPosition, saveCursorPosition } from "../../../utils/cursorSetup";
import {manageDataElement} from "../Mixin/manageDataElement";

export default {
    name: "TextIconOption",
    props: ["item", "settings"],
    mixins: [manageDataElement],
    data() {
        return {
            activeName: 'general',
            fontStyleOptions: [
                { label: 'Bold', value: 'bold' },
                { label: 'Italic', value: 'italic' },
                { label: 'Underline', value: 'underline' },
            ]
        };
    },
    components: {
        Slider,
        ColorInput,
        Alignment,
        Icon,
        Checkbox
    },
    computed: {
        extension() {
            const item = this.item.data.style.iconName;
            const last4 = item.slice(-4);
            return last4.slice(-4) === '.svg' || !last4.includes('.');
        }
    },
    methods:{
        updateText(event) {
            const element = event.target;
            const cursorPosition = saveCursorPosition(element);
            this.item.data.value = element.innerHTML;
            this.$nextTick(() => {
                restoreCursorPosition(element, cursorPosition);
            });
        },
    }
}
</script>
