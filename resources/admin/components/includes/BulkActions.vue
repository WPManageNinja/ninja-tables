<template>
    <div class="alignleft">
        <label for="bulk-action-selector-top" class="screen-reader-text">
            {{ $t('Select bulk action') }}
        </label>

        <select name="action" v-model="model">
            <option value="">{{ $t('Bulk Actions') }}</option>
            <option value="delete">{{ $t('Delete Entries') }}</option>
        </select>

        <el-button size="small" @click.prevent="apply">{{ $t('Apply') }}</el-button>
    </div>
</template>

<script>
    export default {
        name: "BulkActions",
        data() {
            return {
                model: ''
            };
        },
        methods: {
            apply() {
                if (this.model) {
                    this.$emit('handle', this.model);
                }
            }
        }
    };
</script>
