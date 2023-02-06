<template>
    <el-dialog :visible.sync="visible" class="sortable-upgrade-notice">
        <h1 slot="title" class="el-notifications">
            <i class="el-icon-warning text-warning"></i> {{ $t('Upgrade Notice') }}
        </h1>

        <span>
            {{ $t('Your Ninja Tables Pro plugin is outdated. Please upgrade to its latest version.') }}
        </span>
        <br>
        <br>
        <span>
            <a :href="upgradeGuide" target="_blank">{{ $t('Click here') }}</a> {{ $t('to view the upgrade guide.') }}
        </span>
        
    </el-dialog>
</template>

<script>
    export default {
        name: "SortableUpgradeNotice",
        props: ['show'],
        data() {
            return {
                upgradeGuide: window.ninja_table_admin.upgradeGuide
            }
        },
        computed: {
            visible: {
                get() {
                    return this.show;
                },
                set() {
                    this.close();
                }
            }
        },
        methods: {
            close() {
                this.$emit('close');
            }
        }
    }
</script>

<style lang="scss">
    .sortable-upgrade-notice {
        .el-dialog__body {
            padding: 20px;
        }
    }
</style>