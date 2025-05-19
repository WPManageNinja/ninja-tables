<template>
    <el-tabs v-model="activeName" @tab-click="handleClick" class="ninja-tables-component nt_tab_design nt_tab_design_drag mt-[58px]">
        <el-tab-pane :label="$t('Elements')" name="elements" v-if="!manageCell.active">
            <el-collapse accordion v-model="activeNames" @change="handleChange"
                         v-for="(component, index) in initialData.components" :key="component.key" class="accordions nt-design-collapse">
                <el-collapse-item v-if="component.key !== 'container'" :title="component.name" :name="index">
                    <el-row :gutter="20">

                        <draggable
                            :list="component.fields"
                            :group="{ name: 'people', pull: 'clone', put: false }"
                            :clone="customClone"
                            item-key="name"
                            @end="end"
                            class="el-row"
                        >
                            <template #item="{element: item, index: index}" :key="index">
                                <el-col class="element-style" :span="12">
                                    <div :value="(!hasPro && item.has_pro) ? 'Pro' : ''" class="item w-full">
                                        <NinjaButton type="secondary" :disabled="!hasPro && item.has_pro" class="w-full" :class="!hasPro && item.has_pro ? 'pro-component' : ''">
                                            <el-icon :is="getIconComponent(item.icon)" class="icon-component mr-2" >
                                                <component :is="getIconComponent(item.icon)" />
                                            </el-icon>
                                           <span class="!text-xs font-normal"> {{ item.name }}</span>
                                        </NinjaButton>
                                    </div>
                                </el-col>
                            </template>
                        </draggable>
                    </el-row>
                </el-collapse-item>
            </el-collapse>
        </el-tab-pane>
        <el-tab-pane :label="$t('Options')" name="options"
                     v-if="item?.data?.type && !manageCell.active && showOptions">
            <el-collapse v-model="activeOption" accordion @change="accorDianChange" class="accordions nt-design-collapse">
                <el-collapse-item :title="$t(ucWords(item.data.type) + ' Options')" name="1">
                    <text-option v-if="item.data.type === 'text'" :item="item"/>
                    <button-option v-else-if="item.data.type === 'button'" :item="item"></button-option>
                    <star-rating v-else-if="item.data.type === 'star_rating'" :item="item"></star-rating>
                    <icon-option v-else-if="item.data.type === 'icon'" :item="item"></icon-option>
                    <progress-option v-else-if="item.data.type === 'progress'" :item="item"></progress-option>
                    <list-option v-else-if="item.data.type === 'list'" :item="item"
                                 :settings="initialData.settings"></list-option>
                    <custom-html-option v-else-if="item.data.type === 'custom_html'" :item="item"></custom-html-option>
                    <shortcode-option v-else-if="item.data.type === 'shortcode'" :item="item"></shortcode-option>
                    <stylist-list-option v-else-if="item.data.type === 'stylist_list'" :item="item"
                                         :settings="initialData.settings"></stylist-list-option>
                    <image-option v-else-if="item.data.type === 'image'" :item="item"></image-option>
                    <text-icon-option v-else-if="item.data.type === 'text_icon'" :item="item"></text-icon-option>
                    <ribbon-option v-else-if="item.data.type === 'ribbon'" :item="item" :maxWidth="maxWidth"
                                   :settings="initialData.settings"></ribbon-option>
                </el-collapse-item>
                <el-collapse-item :title="$t('Spacing')" name="2" v-if="item.data.type !== 'ribbon'">
                    <spacing-input :types="['margin', 'padding']" :item="item"></spacing-input>
                </el-collapse-item>
            </el-collapse>
        </el-tab-pane>
        <el-tab-pane :label="$t('Settings')" name="settings" v-if="!manageCell.active">
            <el-collapse accordion v-model="activeNames" @change="handleChange"
                         v-for="(setting, key) in initialData.settings"
                         :key="key" class="accordions nt-design-collapse">

                <el-collapse-item :name="key">
                    <template #title>
                        {{ setting.name }}
                        <el-tooltip
                            v-if="setting.key === 'global_styling' || setting.key === 'sticky' || setting.key === 'ace_editor_js'"
                            placement="top-start" effect="light">

                            <template #content>
                                <h3>{{ setting.name }}</h3>

                                <p v-if="setting.key === 'sticky' || setting.key === 'ace_editor_js'">
                                    This is a Pro feature.
                                    <get-pro/>
                                </p>
                                <p v-else>The global style will be applied if the <br> component individual style is not
                                    applied.</p>
                            </template>

                            <el-icon style="margin-left: 2px" class="el-text-info"><InfoFilled /></el-icon>

                        </el-tooltip>
                    </template>

                    <div v-if="setting.key === 'ace_editor_css'" style="margin-right: 3px;" class="ntb-ace-editor">
                        <label>Add Your Custom CSS</label>
                        <p>
                            You may add <code>.ntb_{{ initialData.table_data.id }} </code> as your css selector prefix
                            to target this
                            specific
                            table.
                        </p>
                        <ace_code_editor editor_id="ninja_custom_css" mode="css"
                                         v-model="initialData.settings.custom_css.value">
                        </ace_code_editor>
                        <span>Please don't include <code>&lt;style&gt;&lt;/style&gt;</code> tag</span>
                    </div>

                    <div v-else-if="setting.key === 'ace_editor_js'" style="margin-right: 3px;" class="ntb-ace-editor">
                        <label>Add Your Custom JS</label>
                        <p>
                            You may use <code>.ntb_{{ initialData.table_data.id }} </code> to target this specific
                            table.
                        </p>
                        <ace_code_editor editor_id="ninja_custom_js" mode="javascript"
                                         v-model="initialData.settings.custom_js.value">
                        </ace_code_editor>
                        <span>Please don't include <code>&lt;script>&lt;/script&gt;</code> tag</span>
                    </div>

                    <div v-else class="component-spacing" v-for="(item, tabKey, index) in setting.options"
                         :key="tabKey">
                        <all-input-element :disableResponsive="getBoolean(!hasPro && setting.has_pro)"
                                           :item="item"></all-input-element>

                        <template v-if="item.childs && (getBoolean(item.value))">
                            <div v-for="(singleItem, childKey, childIndex) in item.childs" :key="childKey">
                                <all-input-element :item="singleItem" class="component-spacing"/>
                            </div>
                        </template>
                    </div>
                </el-collapse-item>
            </el-collapse>

            <el-collapse v-model="activeNames" class="accordions nt-design-collapse" @change="handleChange">
                <el-collapse-item :title="$t('Export Table')" class="">
                    <select-input :items="exports.items" v-model="exports.format"
                                  :label="$t('Select Format')"></select-input>
                    <NinjaButton @click="exportTable" class="w-full my-2" size="small">{{ $t('Export') }}</NinjaButton>
                </el-collapse-item>
            </el-collapse>
        </el-tab-pane>

        <el-tab-pane :label="$t('Responsiveness')" name="responsiveness" v-if="!manageCell.active">
            <el-collapse accordion v-model="activeNames" @change="handleChangeResponsive"
                         v-for="(responsiveItem, key) in initialData.responsive" :key="key" class="accordions nt-design-collapse">
                <el-collapse-item :title="responsiveItem.name" :name="key">
                    <div class="component-spacing" v-for="(item, index) in responsiveItem.options" :key="index">
                        <div v-if="'devices' === index">
                            <el-tabs v-model="deviceActiveName" @tab-click="handleDeviceClick" class="nt-design-tabs">
                                <el-tab-pane v-for="(devices, index) in item" :key="index" :label="devices.name"
                                             :name="devices.key">
                                    <div v-for="(device, index, key) in devices" :key="index"
                                         v-if="('name' != index) && ('key' != index)">
                                        <all-input-element :initialData="initialData" :item="device"
                                                           :disableResponsive="!enableResponsive"
                                                           :mobileDisableBreakpoint="devices.key === 'mobile' && isDisableMobileBreakpoint && device.key !== 'disable_breakpoint'"
                                                           :tabletDisableBreakpoint="devices.key === 'tablet' && isDisableTabletBreakpoint && device.key !== 'disable_breakpoint'"
                                                           :deviceName="devices.key"
                                                           class="component-spacing"></all-input-element>
                                    </div>
                                </el-tab-pane>
                            </el-tabs>
                        </div>
                        <div v-else>
                            <all-input-element :item="item"></all-input-element>
                        </div>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </el-tab-pane>
        <el-tab-pane :label="$t('Manage cell')" name="cells" class="p-5" v-if="manageCell.activeTab === 'cells'">
            <cell-setting :manageCell="manageCell" :setting="initialData.settings"></cell-setting>
            <NinjaButton @click="closeCellEditing" type="primary" class="ntb-manage-button mt-2"> {{ $t('Close') }}</NinjaButton>
        </el-tab-pane>
        <el-tab-pane :label="$t('Background')" class="p-5" name="background" v-if="manageCell.activeTab === 'background'">
            <background-color :manageCell="manageCell"></background-color>
            <NinjaButton @click="closeCellEditing" type="primary" class="ntb-manage-button mt-2"> {{ $t('Close') }}</NinjaButton>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import ace_code_editor from '../../../../common/_ace_editor';
import draggable from "vuedraggable";
import AllInputElement from "../SettingComponent/AllInputElement.vue";
import TextOption from "../OptionComponent/TextOption.vue";
import ButtonOption from "../OptionComponent/ButtonOption.vue";
import StarRating from "../OptionComponent/StarRatingOption.vue";
import IconOption from "../OptionComponent/IconOption.vue";
import ProgressOption from "../OptionComponent/ProgressOption.vue";
import ListOption from "../OptionComponent/ListOption.vue";
import CustomHtmlOption from "../OptionComponent/CustomHTML";
import ShortcodeOption from "../OptionComponent/ShortcodeOption";
import StylistListOption from "../OptionComponent/StylistListOption";
import ImageOption from "../OptionComponent/ImageOption";
import SpacingInput from "../SettingComponent/SpacingInput";
import TextIconOption from "../OptionComponent/TextIconOption";
import RibbonOption from "../OptionComponent/RibbonOption";
import ColorInput from "../SettingComponent/ColorInput";
import BackgroundColor from "./_Background";
import CellSetting from "./_CellSetting";
import SelectInput from "../SettingComponent/SelectInput.vue";
import {helpers} from "../Mixin/helpers";
import GetPro from "../../Tools/GetPro";
import {useEventBus} from './../../../eventBus';
import {
    Collection,
    CopyDocument,
    CreditCard,
    Edit,
    EditPen,
    Finished,
    Grid,
    InfoFilled, List, Notebook,
    Operation, Picture, Plus,
    Star
} from "@element-plus/icons-vue";
import NinjaButton from '../../../@ui-utils/NinjaButton.vue';


export default {
    name: "LeftSideBar",
    props: ["initialData", 'singleItem', 'selectedDevice'],
    mixins: [helpers],
    components: {
        InfoFilled,
        Collection,
        CopyDocument,
        CreditCard,
        Edit,
        EditPen,
        Finished,
        Grid,
        Notebook,
        Operation,
        Picture,
        Plus,
        Star,
        List,
        ace_code_editor,
        GetPro,
        SelectInput,
        CellSetting,
        BackgroundColor,
        ColorInput,
        RibbonOption,
        TextIconOption,
        SpacingInput,
        ButtonOption,
        AllInputElement,
        draggable,
        TextOption,
        StarRating,
        IconOption,
        ProgressOption,
        ListOption,
        CustomHtmlOption,
        ShortcodeOption,
        StylistListOption,
        ImageOption,
        NinjaButton
    },
    data() {
        return {
            bus: useEventBus(),
            activeName: "elements",
            activeNames: ["general"],
            deviceActiveName: 'desktop',
            deviceLastSelected: '',
            activeOption: 1,
            item: {},
            manageCell: {},
            maxWidth: {},
            exports: {
                items: [
                    {label: 'CSV', value: 'csv'},
                    {label: 'JSON', value: 'json'}
                ],
                format: 'csv'
            }
        };
    },
    methods: {
        exportTable() {
            location.href = this.downloadLink(this.exports.format);
        },
        downloadLink(format = 'csv') {
            return `${window.ajaxurl}?action=ninja-tables-drag-and-drop-export&table_id=${this.$route.params.table_id}&format=${format}`;
        },
        maximumWidth(tdId) {
            if (jQuery('td#' + tdId).length === 0) {
                return;
            }

            let width = jQuery('td#' + tdId).attr('style').split(';')[1];
            return width.split(' ')[2].split('px')[0];
        },
        end($event) {
            if ($event && $event.to.id) {
                this.maxWidth = this.maximumWidth($event.to.id)
            }
        },
        customClone($event) {
            if (!this.hasPro && $event.has_pro) {
                this.upgradeMessage();
            } else {
                const item = {
                    id: this.id(),
                    data: this.deepClone($event)
                }
                return item;
            }
        },

        handleClick(tab, event) {
            const responsiveTabPress = tab.$options?.propsData?.name === 'responsiveness';
            if (responsiveTabPress) {
                this.deviceLastSelected === '' ? this.$emit('deviceSelected', '') : this.$emit('deviceSelected', this.deviceLastSelected);
            } else {
                this.$emit('deviceSelected', '');
            }

            if (tab.$options?.propsData.name !== 'options') {
                this.bus.emit('manageCell');
            }
        },
        handleChange(val) {
            if (this.deviceLastSelected !== '') {
                this.deviceLastSelected = '';
                this.$emit('deviceSelected', '');
            }
        },
        handleDeviceClick(tab, event) {
            const data = tab.props.name;
            this.deviceActiveName = data;
            this.deviceLastSelected = data;
            this.$emit('deviceSelected', data);
        },
        handleChangeResponsive(val) {
            if (val === 'responsive_settings' && this.deviceActiveName === 'desktop') {
                this.deviceActiveName = 'tablet';
            }
            this.deviceLastSelected = this.deviceActiveName;
            this.$emit('deviceSelected', this.deviceActiveName);
        },
        accorDianChange(val) {
            this.activeOption = val
        },
        manageRowColumns() {
            this.bus.on("manage-cell", (items) => {
                // received event from layout component
                if (items.active) {
                    this.manageCell = items
                    this.activeName = items.activeTab
                } else {
                    this.closeCellEditing();
                }
            });
        },
        closeCellEditing() {
            this.manageCell = false
            this.activeName = "elements";
            this.bus.emit('manageCell');
        },
        handleUpdateItem(updatedItem) {
            this.item = updatedItem;
        },
        getIconComponent(iconName) {
            if (!iconName) {
                return Grid;
            }

            const iconMapping = {
                'edit-outline': EditPen,
                'edit': Edit,
                's-grid': Grid,
                'bank-card': CreditCard,
                'finished': Finished,
                'star-off': Star,
                's-operation': Operation,
                'document-copy': CopyDocument,
                'picture-outline': Picture,
                'document': Document,
                'collection-tag': Collection,
                'circle-plus-outline': Plus,
                'notebook-1': Notebook,
                'notebook-2': List,
            }

            return iconMapping[iconName].name;
        }
    },
    created() {
        this.manageRowColumns();

        this.bus.on("closeManageCell", () => {
            this.closeCellEditing();
        });

        this.bus.on('singleTdId', (tdId) => {
            this.maxWidth = this.maximumWidth(tdId)
        });
    },
    computed: {
        hasPro() {
            return !!window.ninja_table_admin.hasPro;
        },
        enableResponsive() {
            const responsiveEnableStatus = this.initialData.responsive.general.options.enable_responsive_table.value;
            return this.getBoolean(responsiveEnableStatus);
        },
        showOptions() {
            // note: when admin is in the options tab then we can only show options tab, otherwise don't.
            return this.activeName === 'options';
        },
        responsiveDevice() {
            return this.initialData.responsive.mode_options.options.devices;
        },
        isDisableMobileBreakpoint() {
            let isDisableMobileBreakpoint = this.responsiveDevice.mobile.disable_breakpoint.value;
            return this.getBoolean(isDisableMobileBreakpoint);
        },
        isDisableTabletBreakpoint() {
            let isDisableTabletBreakpoint = this.responsiveDevice.tablet.disable_breakpoint.value;
            return this.getBoolean(isDisableTabletBreakpoint);
        },
    },
    watch: {
        singleItem: {
            handler(newValue, oldValue) {
                if (newValue) {
                    this.activeOption = this.activeOption === '2' ? "2" : "1"
                    this.activeName = "options";
                    this.item = newValue.item
                } else {
                    this.activeName = "elements";
                }
            },
            deep: true
        },
        'initialData.settings.custom_css.value': {
            handler(newValue) {
                if (newValue) {
                    let tableId = this.initialData.table_data.id;
                    let styleId = `ninja_table_builder_custom_css_${tableId}`;
                    let style = {
                        type: 'text/css',
                        style: document.querySelector(`style[data-id="${styleId}"]`) || document.createElement('style'),
                        content: newValue,
                        append: function () {
                            this.style.setAttribute('data-id', styleId);
                            this.style.innerHTML = this.content;
                            if (!document.querySelector(`style[data-id="${styleId}"]`)) {
                                document.head.appendChild(this.style);
                            }
                        }
                    };
                    style.append();
                }
            },
            deep: true
        },
        'initialData.settings.custom_js.value': {
            handler(newValue) {
                let tableId = this.initialData.table_data.id;
                let scriptId = `ninja_table_builder_custom_js_${tableId}`;
                let script = {
                    type: 'text/javascript',
                    script: document.querySelector(`script[data-id="${scriptId}"]`) || document.createElement('script'),
                    content: newValue,
                    append: function () {
                        this.script.setAttribute('data-id', scriptId);
                        this.script.innerHTML = this.content;
                        if (!document.querySelector(`script[data-id="${scriptId}"]`)) {
                            document.body.appendChild(this.script);
                        }
                    }
                };
                script.append();
            },
            deep: true
        },
    }
};
</script>
<style lang="scss">
.ntb-ace-editor {
    & .ninja_custom_css_editor {
        min-height: 200px;
    }

    & .ninja_css_errors,
    & .ninja_javascript_errors {
        display: none;
    }
}

.ninja-tables-component {

    // .el-collapse-item__content {
    //     padding-bottom: 6px;
    // }

    // .el-slider__runway {
    //     margin-left: 12px;
    // }

    // .el-slider {
    //     margin-left: 10px;
    // }

    .element-style {
        padding: 10px 0px;
        display: flex;
        align-items: center;
        justify-content: center;

        .button-component {
            width: 120px;
            padding: 15px 15px;

            i {
                font-size: 15px;
            }

            &:hover {
                cursor: move;
            }
        }

        .pro-component {
            &:hover {
                cursor: not-allowed;
            }
        }

        // .item {
        //     .el-badge__content {
        //         right: 33px;
        //     }
        // }
    }

    // .ntb-manage-button {
    //     display: block;
    //     margin: 10px auto;
    //     padding: 10px 20px
    // }

    .component-spacing,
    .component-wrapper > * {
        padding: 6px 0px;
    }
}
</style>
