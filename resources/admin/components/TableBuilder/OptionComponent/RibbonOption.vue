<template>
    <div>
        <el-tabs v-model="activeTabName">
            <el-tab-pane class="component-wrapper" :label="$t('General')" name="general">
                <span>{{ $t('Ribbon Type') }}</span>
                <el-select v-model="item.data.style.ribbonType" style="width: 97%; margin-bottom: 7px;" size="mini">
                    <el-option
                        v-for="item in ['side', 'bookmark', 'corner', 'horizontal']"
                        :key="item"
                        :label="item"
                        :value="item">
                    </el-option>
                </el-select>
                <p v-if="item.data.style.ribbonType === 'corner'">{{ $t('Ribbon Position') }}</p>
                <el-select
                    size="mini"
                    @change="leftToRight"
                    v-if="item.data.style.ribbonType === 'corner'"
                    v-model="item.data.style.ribbonPosition" placeholder="Select">
                    <el-option
                        v-for="item in ['left', 'right']"
                        :key="item"
                        :label="item"
                        :value="item">
                    </el-option>
                </el-select>
                <!--       ##### all about corner #####-->
                <slider
                    v-if="item.data.style.ribbonType === 'corner'"
                    v-model="item.data.style.width"
                    :label="$t('Ribbon Width')"
                    :max="500"
                    :min="100"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'corner'"
                    v-model="item.data.style.height"
                    :label="$t('Ribbon Height')"
                    :max="60"
                    :min="10"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'corner'"
                    v-model="item.data.style.cornerXAxis"
                    :label="$t('X Offset')"
                    :max="cornerXAxis"
                    :min="-30"
                    :step="1"
                ></slider>
                <!--        ##### all about side ribbon ####-->
                <slider
                    v-if="item.data.style.ribbonType === 'side'"
                    v-model="item.data.style.sideWidth"
                    :label="$t('Ribbon Width')"
                    :max="sideMaxWidth"
                    :min="20"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'side'"
                    v-model="item.data.style.sideHeight"
                    :label="$t('Ribbon Height')"
                    :max="100"
                    :min="10"
                ></slider>
                <!--        ##### all about bookmark #####-->
                <slider
                    v-if="item.data.style.ribbonType === 'bookmark'"
                    v-model="item.data.style.bookmarkWidth"
                    :label="$t('Ribbon Width')"
                    :max="bookmarkMaxWidth"
                    :min="30"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'bookmark'"
                    v-model="item.data.style.bookmarkHeight"
                    :label="$t('Ribbon Height')"
                    :max="200"
                    :min="5"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'bookmark'"
                    v-model="item.data.style.xAxis"
                    :label="$t('X Offset')"
                    :max="bookmarkXAxis"
                    :min="-10"
                    :step="1"
                ></slider>
                
                <!--        #### all about horizontal #####-->
                <slider
                    v-if="item.data.style.ribbonType === 'horizontal'"
                    v-model="item.data.style.horizontalWidth"
                    :label="$t('Ribbon Width')"
                    :max="horizontalMaxWidth"
                    :min="30"
                ></slider>
                <slider
                    v-if="item.data.style.ribbonType === 'horizontal'"
                    v-model="item.data.style.horizontalHeight"
                    :label="$t('Ribbon Height')"
                    :max="100"
                    :min="5"
                ></slider>
                <!--        ***** common *****-->
                <slider
                    v-model="item.data.style.yAxis"
                    :label="$t('Y Offset')"
                    :max="150"
                    :min="-150"
                    :step="1"
                ></slider>
                <slider
                    v-model="settings.global_styling.options.margin_top.value"
                    :label="$t('Table margin top')"
                    :max="item.data.style.horizontalHeight"
                    :min="0"
                ></slider>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('Text')" name="text">
                <el-input
                    size="mini"
                    type="text"
                    :placeholder="$t('Ribbon Text')"
                    v-model="item.data.value"
                    style="width: 96%; margin-bottom: 7px;"
                ></el-input>
                
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
                <slider
                    v-model="item.data.style.textXAxis"
                    :label="$t('Font X Offset')"
                    :max="100"
                    :min="-100"
                    :step="1"
                ></slider>
                <slider
                    v-model="item.data.style.textYAxis"
                    :label="$t('Font Y Offset')"
                    :max="100"
                    :min="-100"
                    :step="1"
                ></slider>
                <checkbox
                    :label="$t('Font Style')"
                    :options="fontStyleOptions"
                    v-model="item.data.style.fontWeight"
                ></checkbox>
            </el-tab-pane>
            <el-tab-pane class="component-wrapper" :label="$t('Background')" name="background">
                <color-input
                    :label="$t('Background color')"
                    v-model="item.data.style.backgroundColor"
                ></color-input>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import ColorInput from "../SettingComponent/ColorInput";
import Slider from "../SettingComponent/SliderInput";
import Checkbox from '../SettingComponent/CheckboxInput'

export default {
    name: "RibbonOption",
    props: ['item', 'maxWidth', 'settings'],
    data() {
        return {
            cellPadding: this.settings.general.options.cell_padding.value,
            activeTabName: 'general',
            fontStyleOptions: [
                {label: 'Bold', value: 'bold'},
                {label: 'Italic', value: 'italic'},
                {label: 'Underline', value: 'underline'},
            ]
        }
    },
    methods: {
        leftToRight() {
            let width = 0;
            if (this.item.data.style.ribbonPosition === 'right') {
                width = this.cornerMaxWidth - 10
            } else {
                width = -13;
            }
            this.item.data.style.cornerXAxis = width;
        }
    },
    computed: {
        cornerMaxWidth(){
            return Number(this.maxWidth) + Number( (this.cellPadding - 10) * 2)  - 100
        },
        cornerXAxis(){
            return Number(this.maxWidth) + Number( (this.cellPadding - 10) * 2)  - 100
        },
        bookmarkXAxis() {
            return Number(this.maxWidth) + Number(6) + Number( (this.cellPadding - 10) * 2)  - Number(this.item.data.style.bookmarkWidth)
        },
        horizontalMaxWidth() {
            return Number(this.maxWidth) + Number(20) + Number( (this.cellPadding - 10) * 2)
        },
        sideMaxWidth() {
            return Number(this.maxWidth) + Number( (this.cellPadding - 10) * 2)
        },
        bookmarkMaxWidth() {
            return Number(this.maxWidth) + Number(16) + Number( (this.cellPadding - 10) * 2)
        }
    },
    components: {
        ColorInput,
        Slider,
        Checkbox
    }
}
</script>
