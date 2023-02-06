<template>
    <div class="remove">
        <el-popover ref="popover5" placement="top" width="160" v-model="visible">
            <p>{{ $t('Are you sure to delete this?') }}</p>
            <div style="text-align: right; margin: 0">
                <el-button size="mini" type="text" @click="visible = false">
                    {{ $t('cancel') }}
                </el-button>
                <el-button type="danger" size="mini" @click="proceedConfirmation">
                    {{ $t('confirm') }}
                </el-button>
            </div>
        </el-popover>

        <span v-if="label" v-popover:popover5 v-text="label" class="span-block"></span>
        <a v-else v-popover:popover5>
            <span class="dashicons dashicons-trash"></span>
        </a>
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