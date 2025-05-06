<template>
    <div :style="[margin]" class="ntb-datas-wrapper">
        <NinjaTextEditor class="hover-item" v-if="item.data.type === 'text'" :value="item.data.value" @input="updateTextContent"
            :style="[padding, fontWeight, fontSize, displayBlock, textAlign, color, textStyle]" />

        <span v-else-if="item.data.type === 'button'" class="hover-item"
            :style="[justifyContent, { display: item.data.style.fullWidth ? 'block' : 'flex' }]">
            <a @click.prevent :href="`${item.data.style.url}`"
                :style="[justifyContent, displayFlex, { 'text-decoration': 'none' }]"
                :target="item.data.style.newTab ? '_blank' : ''" :rel="linkAttributes">
                <el-button class="ntb-el-button" type="primary"
                    :style="[buttonIconHover, fontSize, color, backgroundColor, border, buttonStyle]"
                    :onmouseover="mouseOver" :onmouseout="mouseOut">
                    <span class="button-content-wrapper" :style="[padding]">
                        <span class="svgIcon"
                            v-if="item.data.style.iconPosition === 'left' && item.data.style.enableIcon"
                            :style="[iconWithOtherComponent, textAlign]">
                        </span>
                        <NinjaTextEditor class="hover-item" toolbar="bold italic underline" :value="item.data.value"
                            @input="updateTextContent" :style="[fontWeight, {
                                'margin-left': item.data.style.iconPosition === 'left' ? item.data.style.itemSpacing + 'px' : '0px',
                                'margin-right': item.data.style.iconPosition === 'right' ? item.data.style.itemSpacing + 'px' : '0px'
                            }]" />
                        <span class="svgIcon"
                            v-if="item.data.style.iconPosition === 'right' && item.data.style.enableIcon"
                            :style="[iconWithOtherComponent, textAlign]">
                        </span>
                    </span>
                </el-button>
            </a>
        </span>

        <div class="hover-item" v-else-if="item.data.type === 'custom_html'" v-html="item.data.value"
            contenteditable="true" @input="updateContent" :style="[padding]"></div>

        <span class="ninja_table_builder_shortcode hover-item" v-else-if="item.data.type === 'shortcode'"
            v-html="item.data.value" contenteditable="true" @input="updateContent"
            :style="[textAlign, padding, { display: 'block' }]">
        </span>
        <span v-else-if="item.data.type === 'star_rating'">
            <el-rate class="ntb-rating hover-item" :score-template="scoreTemplate" v-model="ratingValue" allow-half
                :show-score="showRatingScore"
                :text-color="`${item.data.style.color === '' ? setting.global_styling.options.color.value : item.data.style.color}`"
                :max="item.data.style.maxStar" :colors='starRatingStyling'
                :style="[ratingStyle, textAlign, padding]"></el-rate>
        </span>
        <span v-else-if="item.data.type === 'icon'">
            <span :style="[padding, displayBlock]" class="hover-item">
                <span class="svgIcon" :style="[iconStyle, displayBlock, dBlockAlign]"></span>
            </span>
        </span>

        <span class="ntb-progress hover-item" v-else-if="item.data.type === 'progress'"
            :style="[padding, displayBlock]">
            <el-progress :type="item.data.style.type"
                :style="[displayBlock, dBlockAlign, progressBarTextStyle, { 'width': this.item.data.style.type === 'circle' ? item.data.style.width + 'px' : '100%' }]"
                v-if="Number(item.data.style.percentage)" :width="Number(item.data.style.width)"
                :percentage="Number(item.data.style.percentage)" :color="[color]"
                :stroke-width="Number(item.data.style.thickness)">
            </el-progress>
        </span>
        <span v-else-if="item.data.type === 'image'">
            <a class="hover-item" @click.prevent v-bind="[hrefAttribute, targetAttribute]" :style="[displayBlock]"
                :rel="linkAttributes">
                <img :src="item.data.value"
                    :style="[displayBlock, dBlockAlign, padding, borderRadius, { 'width': this.item.data.style.size + '%', }]"
                    :alt="`${item.data.style.alt}`" />
            </a>
        </span>
        <span v-else-if="item.data.type === 'list' || item.data.type === 'stylist_list'"
            :style="[textAlign, displayFlex, justifyContent]" class="ntb-list hover-item">
            <component :is="listType" :class="!manage ? 'ntb-list-style' : ''" :style="[listStyle, padding]">
                <li v-for="(val, index) in item.data.value" :key="index" :style="[color, fontSize, lineHeight]">
                    <span class="svgIcon" v-if="item.data.type === 'stylist_list'"
                        :style="[iconWithOtherComponent, textAlign, verticalAlignMiddle]">
                    </span>
                    <NinjaTextEditor :value="val" @input="updateListItem(index, $event)"
                        :style="[fontWeight, verticalAlignMiddle, { 'margin-left': item.data.style.itemSpacing + 'px' }]" />

                    <div class="icon-styles remove-elements" v-if="!manage">
                        <el-icon @click.stop="copyItem(index)"><CopyDocument /></el-icon>
                        <el-icon @click.stop="deleteItem(index)"><Delete /></el-icon>
                    </div>

                </li>
            </component>
        </span>
        <span v-else-if="item.data.type === 'text_icon'" :style="[displayFlex, justifyContent]" class="hover-item">
            <span class="icon-text-wrapper" :style="[padding, fontWeight, fontSize, displayBlock, textAlign, color]">
                <span class="svgIcon" v-if="item.data.style.iconPosition === 'left'"
                    :style="[iconWithOtherComponent, textAlign, verticalAlignMiddle]">
                </span>
                <NinjaTextEditor class="hover-item" :value="item.data.value" @input="updateTextContent"
                    :style="`vertical-align: middle;display: inline-block; margin-left: ${item.data.style.iconPosition === 'left' ? item.data.style.itemSpacing : 0}px; margin-right: ${item.data.style.iconPosition === 'right' ? item.data.style.itemSpacing : 0}px;`" />
                <span class="svgIcon" v-if="item.data.style.iconPosition === 'right'"
                    :style="[iconWithOtherComponent, textAlign, verticalAlignMiddle]">
                </span>
            </span>
        </span>
        <span v-else-if="item.data.type === 'ribbon'" style="position:relative;margin:0;padding:0;width:100%;">
            <div class="ribbon-wrapper" :style="[{ top: yAxisRibbon, left: xAxisRibbon }]">
                <div :class="[item.data.style.ribbonType, item.data.style.ribbonType === 'bookmark' ? 'up' : '']">
                    <div :class="['content', item.data.style.ribbonPosition === 'left' ? 'left' : 'right']"
                        :style="[ribbonSize, backgroundColor, { position: '' }, { 'text-align': 'center', padding: item.data.style.ribbonType === 'corner' ? item.data.style.height + 'px 0px' : '' }]">
                        <p contenteditable="true" @input="updateContent" :style="[fontSize, color, fontWeight, {
                            'margin-top': item.data.style.textYAxis + 'px',
                            'margin-left': item.data.style.textXAxis + 'px'
                        }]">{{ item.data.value }}</p>
                    </div>
                </div>
            </div>
        </span>
    </div>
</template>

<script>
import { restoreCursorPosition, saveCursorPosition } from "../../../utils/cursorSetup";
import NinjaTextEditor from "../../Extras/_NinjaTextEditor.vue";
import { manageDataElement } from "../Mixin/manageDataElement";
import {CopyDocument, Delete} from "@element-plus/icons-vue";
export default {
    name: "Datas",
    mixins: [manageDataElement],
    props: ["item", 'manage', 'setting', 'reference', 'row'],
    components: {
        Delete,
        CopyDocument,
        NinjaTextEditor
    },
    data() {
        return {
            editorRef: `ninja-table-editor-${this.reference}`,
            listType: this.item.data.style.listType || 'ul'
        };
    },
    methods: {
        getAsset(path) {
            if (path.includes(window.location.origin)) {
                return path;
            } else {
                return ninja_table_admin.ninja_tables_pro_url + '/assets/libs/icons/' + path + '.svg';
            }
        },
        updateTextContent(newValue, idx) {
            if (idx !== undefined) {
                this.item.data.value[idx] = newValue;
            } else {
                this.item.data.value = newValue;
            }
        },
        updateListItem(index, newValue) {
            this.item.data.value[index] = newValue;
        },
        updateContent(event) {
            // const $ref = this.$refs.ninja_table_text_editor;
            const cursorPosition = saveCursorPosition(event.target);
            this.item.data.value = event.target.innerHTML;
            this.$nextTick(() => {
                restoreCursorPosition(event.target, cursorPosition);
            });
            
        },
        copyItem(index) {
            this.item.data.value.splice(index + 1, 0, this.item.data.value[index]);
        },
        deleteItem(index) {
            this.item.data.value.splice(index, 1);
        },
    },
    computed: {
        ratingValue: {
            get() {
                return Number(this.item.data.value);
            },
            set(newValue) {
                this.item.data.value = newValue;
            }
        },
        mouseOver() {
            if (this.item.data.style.isHover === true) {
                let hoverColor = this.item.data.style.hoverColor;
                let hover = hoverColor === '' ? this.setting.global_styling.options.color.value : hoverColor;
                return [
                    "this.style.background='" + this.item.data.style.hoverBackgroundColor + "'",
                    "this.style.color='" + hover + "'",
                    "this.style.border='" + this.item.data.style.hoverBorderSize + 'px solid' + this.item.data.style.hoverBorderColor + "'",
                    "this.style.transform='scale(" + this.item.data.style.transition + ")'"
                ];
            }
        },
        mouseOut() {
            if (this.item.data.style.isHover === true) {
                let iconColor = this.item.data.style.iconColor;
                let unHover = (iconColor === '' || iconColor === '#000000') ? this.setting.global_styling.options.color.value : iconColor;
                return [
                    "this.style.background='" + this.item.data.style.backgroundColor + "'",
                    "this.style.color='" + unHover + "'",
                    "this.style.border='" + this.item.data.style.borderSize + 'px solid' + this.item.data.style.borderColor + "'",
                    "this.style.transform='scale(1)'"
                ];
            }
        }
    }
};
</script>
<style lang="scss">
.ribbon-wrapper {
    position: relative;
    max-width: 600px;
    width: 90%;
    z-index: 1;

    .corner {
        width: 120px;
        height: 100px;
        overflow: hidden;
        position: absolute;

        .content {
            position: absolute;
            display: block;
            text-align: center;
            box-shadow: 0px 1px 2px rgba(black, 0.5);
        }

        .left {
            right: -25px;
            top: 30px;
            transform: rotate(-45deg);
        }

        .right {
            left: -25px;
            top: 30px;
            transform: rotate(45deg);
        }
    }

    .bookmark {
        position: absolute;
        overflow: hidden;
        filter: drop-shadow(2px 3px 2px rgba(black, 0.5));

        >.content {
            font-size: 1.25rem;
            text-align: center;
            font-weight: 400;
            padding: 8px 2px 4px;
        }

        &.up>.content {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% calc(100% - 8px), 0 100%);
        }
    }

    .side {
        position: relative;
        float: left;
        background-size: cover;

        .content {
            position: absolute;
            padding: 8px 10px;
            box-shadow: 1px 2px 3px rgba(black, 0.5);

            &:before {
                width: 7px;
                height: 100%;
                top: 0;
                left: -6.5px;
                padding: 1px 0 7px;
                background: inherit;
                border-radius: 5px 0 0 5px;
                content: "";
                position: absolute;
            }

            &:after {
                width: 5px;
                height: 5px;
                bottom: -5px;
                left: -4.5px;
                background: lightblue;
                border-radius: 5px 0 0 5px;
                content: "";
                position: absolute;
            }
        }
    }

    .horizontal {
        position: relative;
        box-shadow: 1px 2px 3px rgba(black, 0.5);

        .content {
            position: absolute;
        }
    }
}

.ntb-datas-wrapper {

    .svgIcon {
        display: inline-block;
        mask-size: cover;
        flex: none;
        -webkit-mask-size: cover;
    }

    .ntb-progress {
        .el-progress {
            .el-progress__text {
                color: var(--progress-bar-text-color);
                font-size: var(--progress-bar-font-size) !important;
            }
        }
    }

    .ntb-rating {
        height: auto;

        .el-rate__item {
            i {
                font-size: var(--rating-font-size)
            }
        }

        .el-rate__text {
            display: block;
            font-size: var(--rating-font-size)
        }
    }

    .ntb-list {

        ul,
        ol {
            margin: 0;
        }
    }

    dd,
    li {
        margin-bottom: initial;
    }

    .ntb-list-style {
        li {
            position: relative;
            border: 1px solid transparent;
            transition: .1s border;

            .icon-styles {
                position: absolute;
                height: auto;
                width: auto;
                opacity: 0;
                z-index: 1;
                justify-content: flex-end;
                font-size: 12px;
                color: #ffffff;
                visibility: hidden;

                i {
                    background: #3f9eff;
                    padding: 0px 1px;
                    font-weight: bold;
                }
            }

            &:hover {
                border-color: #3f9eff;
                cursor: pointer;

                .icon-styles {
                    top: -17px;
                    opacity: 1;
                    right: 0;
                    visibility: visible;
                }
            }
        }
    }


    .button-content-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-wrap: break-word;
        white-space: break-spaces;
    }

    .icon-text-wrapper {
        line-height: 1.2;
        justify-content: center;
        align-items: center;
    }

    .ntb-el-button {
        padding: var(--padding-dashboard);
    }

    .ntb-el-button:hover {
        .svgIcon {
            background-color: var(--icon-color-hover) !important;
        }
    }

    p,
    span {
        &:focus-visible {
            outline: none;
        }
    }
}
</style>
