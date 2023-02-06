<template>
    <div class="ninja_Language_settings">
        <div class="ninja_header">
            <h2>Language Settings</h2>
            <div class="ninja_actions_action">
                <el-button size="small" type="primary" @click="storeSettings()"> {{ $t('Update Configuration') }}</el-button>
            </div>
        </div>
        <div class="ninja_style_wrapper">
            <div class="section_block">
                <div class="language_block">
                    <div class="form_group">
                        <label for="no_result_text">Empty Results Text:</label>
                        <input v-model="tableSettings.no_result_text" id="no_result_text" type="text" class="form_control"
                               autocomplete="off">
                        <small>The text to display if the table contains no rows.</small>
                    </div>
                    <div class="form_group">
                        <label for="search_box_placeholder">Search Box Placeholder Text</label>
                        <input v-model="tableSettings.search_placeholder" id="search_box_placeholder" type="text"
                               class="form_control" autocomplete="off">
                        <small>Search Box Placeholder</small>
                    </div>
                    <div class="form_group">
                        <label for="search_box_in">Search Dropdown Heading</label>
                        <input v-model="tableSettings.search_in_text" id="search_box_in" type="text" class="form_control"
                               autocomplete="off">
                        <small>Search Dropdown Box Title</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'ninja_language_settings',
        props: ['tableSettings'],
        methods: {
            storeSettings() {
                this.$emit('storeSettings');
            }
        }
    }
</script>
