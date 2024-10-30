<template>
    <div v-if="appReady" class="image_selector">
        <template v-if="column.link_type == 'hyperlinked' || column.link_type == 'none' || column.link_type == 'image_light_box' || column.link_type == 'iframe_ligtbox'">
            <div class="nt_form_group">
                <label>Upload Image</label>
                <div class="nt_form_input">
                    <div v-if="data.image_thumb" class="image_preview">
                        <img :src="data.image_thumb" />
                    </div>
                    <el-button class="image_select_button" @click="initUploader" size="mini" type="info">
                        <span v-if="data.image_thumb">Change Image</span>
                        <span v-else>Upload Image</span>
                    </el-button>

                    <el-button v-if="data.image_thumb" @click="remove" class="image_select_button" size="mini" type="warning">
                        Remove Image
                    </el-button>
                </div>
            </div>
            <div v-if="column.link_type == 'hyperlinked' || column.link_type == 'iframe_ligtbox'" class="nt_form_group">
                <label v-if="column.link_type == 'iframe_ligtbox'">Iframe URL (Only The URL)</label>
                <label v-else>Target URL</label>
                <div class="nt_form_input">
                    <el-input type="url" size="mini" placeholder="Permalink" v-model="data.permalink" />
                </div>
            </div>
        </template>
    </div>
</template>

<script type="text/babel">
import each from 'lodash/each';

export default {
        name: 'maybe_multi_select',
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
