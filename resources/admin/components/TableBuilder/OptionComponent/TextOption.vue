<template>
    <div class="component-wrapper">
        <div style="display: flex; justify-content: end;">
            <button :class="`visual-mode ${visualMode === 'visual' && 'active-visual'}`"
                @click="visualMode = 'visual'">Visual</button>
            <button :class="`visual-mode ${visualMode === 'text' && 'active-visual'}`"
                @click="visualMode = 'text'">Text</button>
        </div>
        <el-input v-if="visualMode === 'text'" type="textarea" :autosize="{ minRows: 3 }"
            :placeholder="$t('Enter here')" v-model="item.data.value" />
        <div class="visual-box" v-else-if="visualMode === 'visual'" @input="updateText" contenteditable="true"
            v-html="item.data.value">
        </div>
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
import { restoreCursorPosition, saveCursorPosition } from "../../../utils/cursorSetup";

export default {
    name: "TextOption",
    props: ["item"],
    components: { Checkbox, ColorInput, SliderInput, Alignment },
    data() {
        return {
            fontStyleOptions: [
                { label: 'Bold', value: 'bold' },
                { label: 'Italic', value: 'italic' },
                { label: 'Underline', value: 'underline' },
            ],
            visualMode: 'visual'
        };
    },
    methods: {
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
<style scoped>
.component-spacing {
    margin-bottom: 10px;
}

.visual-mode {
    /* padding: 3px 10px !important; */
    font-size: 12px;
    border-radius: 0;
    background-color: transparent;
    border: none;
    border-bottom: 1px solid lightgray;
    margin: 0 2px;
    border-radius: 0;
    cursor: pointer;
}

.active-visual {
    background-color: #409EFf;
    color: white;
    border-bottom: 1px solid #409EFF;
    border-radius: 3px;

}

.visual-box {
    margin-top: 6px;
    border: 1px solid lightgray;
    border-radius: 5px;
    padding: 12px !important;
}
</style>