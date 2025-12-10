<template>
    <div v-if="appReady">
        <div class="flex gap-4">
            <div v-if="data.image_thumb" class="mr-4">
                <img class="rounded-[10px]" width="100" height="100" :src="data.image_thumb" alt="image"/>
            </div>

            <div class="flex gap-4">
                <div class="flex items-center gap-2 my-2">
                    <NinjaButton
                        v-if="!data.image_thumb"
                        type="secondary"
                        :icon="assetUrl('icons/upload-02.svg')"
                        @click="initUploader"
                        :btn-text="$t('Image Upload')"
                    />
                    <NinjaButton
                        v-if="data.image_thumb"
                        type="danger"
                        class-names="text-[#FB3748] border-[#FB3748] py-[7px] font-[300] bg-white hover:bg-white"
                        @click="remove"
                        :btn-text="$t('Remove')"
                    />
                    <NinjaButton
                        v-if="data.image_thumb"
                        type="secondary"
                        class-names="font-[300] py-[7px] ms-3"
                        @click="initUploader"
                        :btn-text="$t('Change')"
                    />
                </div>

                <div
                    v-if="column.link_type === 'hyperlinked' || column.link_type === 'iframe_ligtbox' && data.image_thumb">
                    <label v-if="column.link_type === 'iframe_ligtbox'"
                           class="nt-form-modal">{{ $t('Iframe URL (Only The URL)') }}</label>
                    <label v-else class="nt-form-modal">{{ $t('Target URL') }}</label>
                    <NinjaInput
                        type="url"
                        size="small"
                        placeholder="Permalink"
                        v-model="data.permalink"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
import each from 'lodash/each';
import NinjaButton from "../admin/@ui-utils/NinjaButton.vue";
import {assetUrl} from "../admin/utils/ninjatablesadmin";
import NinjaInput from "../admin/@ui-utils/NinjaInput.vue";

export default {
        name: 'maybe_multi_select',
    components: {NinjaInput, NinjaButton},
        props: ['newColumn', 'column', 'adding_counter'],
        data() {
            return {
                appReady: false,
                upload_action: '',
                data: {},
                preferedThum : window.ninja_table_admin.prefered_thumb
            }
        },
        watch: {
            adding_counter() {
                this.newColumn[this.column.key] = {
                    permalink: '',
                    image_thumb: '',
                    image_full: ''
                };
                this.data = this.newColumn[this.column.key];
            }
        },
        methods: {
            assetUrl,
            beforeAvatarUpload(file) {
                if (file.type == 'image/jpg' || file.type == 'image/gif' || file.type == 'image/png') {
                    return true;
                }
                this.$message.error('Image need to be jpg/png/gif format');
                return false;
            },
            handleAvatarSuccess(res, file) {
                // console.log(res);
            },
            initUploader(event) {
                var that = this;
                var send_attachment_bkp = wp.media.editor.send.attachment;
                wp.media.editor.send.attachment = function (props, attachment) {
                    that.newColumn[that.column.key]['alt_text'] = attachment.alt || attachment.title;
                    that.newColumn[that.column.key]['image_full'] = attachment.url;
                    that.newColumn[that.column.key]['image_thumb'] = that.getThumb(attachment);
                    wp.media.editor.send.attachment = send_attachment_bkp;
                }
                wp.media.editor.open();
                return false;
            },
            initFileUploader(event) {
                var that = this;
                var send_attachment_bkp = wp.media.editor.send.attachment;
                wp.media.editor.send.attachment = function (props, attachment) {
                    that.newColumn[that.column.key]['permalink'] = attachment.url;
                    wp.media.editor.send.attachment = send_attachment_bkp;
                }
                wp.media.editor.open();
                return false;
            },
            getThumb(attachment) {
                let highestSize = attachment.width;
                let maybeUrl = attachment.url;
                let finalUrl = false;
                if(this.preferedThum == 'original') {
                    return attachment.url;
                }
                each(attachment.sizes, (image, name) => {
                    if(name == this.preferedThum) {
                        finalUrl = image.url;
                    }
                    if(!finalUrl || image.width > 300) {
                        if (image.width < 400) {
                            finalUrl = image.url;
                        } else if (image.width < highestSize) {
                            highestSize = image.width;
                            maybeUrl = image.url;
                        }
                    }
                });

                return finalUrl || maybeUrl;
            },
            preinitUploader() {
                let oldValue = this.newColumn[this.column.key];
                let data = {
                    permalink: '',
                    image_thumb: '',
                    image_full: ''
                }
                if (!oldValue) {
                    this.newColumn[this.column.key] = data;
                } else if (typeof oldValue == 'string') {
                    this.newColumn[this.column.key] = data;
                }
                this.data = this.newColumn[this.column.key];
            },
            remove() {
                this.data.image_thumb = '';
            }
        },
        mounted() {
            this.preinitUploader();
            this.appReady = true;
        }
    }
</script>
