<template>
    <div class="remove">
        <el-popover
            placement="top"
            width="160"
            v-model:visible="visible"
            trigger="click"
        >
            <template #default>
                <p>{{ $t('Are you sure to delete this?') }}</p>
                <div style="text-align: right; margin: 0">
                    <el-button size="small" link @click="visible = false">
                        {{ $t('cancel') }}
                    </el-button>
                    <el-button type="danger" size="small" @click="proceedConfirmation">
                        {{ $t('confirm') }}
                    </el-button>
                </div>
            </template>
            <template #reference>
                <span v-if="label" class="span-block">{{ label }}</span>
                <a v-else>
                    <span class="dashicons dashicons-trash"></span>
                </a>
            </template>
        </el-popover>
    </div>
</template>

<script>
    export default {
        name: 'DeletePopOver',

        props: ['label'],

        data() {
            return {
                visible: false
            }
        },

        methods: {
            proceedConfirmation() {
                this.visible = false;

                this.$emit('deleted');
            }
        }
    }
</script>

<style scoped>
    .remove {
        display: inline-block;
    }
</style>
