<template>
    <table class="ninja_filter_table">
        <thead>
            <tr>
                <th></th>
                <th>Label</th>
                <th>Filter Value</th>
            </tr>
        </thead>
        <draggable
                :options="{handle:'.handle'}"
                :list="value"
                :element="'tbody'"
        >
            <tr v-for="(filter, index) in value">
                <td>
                    <span style="margin-top: 10px" class="dashicons dashicons-editor-justify handle"></span>
                </td>
                <td>
                    <el-input size="mini" v-model="filter.label" type="text"></el-input>
                </td>
                <td>
                    <el-input size="mini" v-model="filter.value" type="text"></el-input>
                </td>
                <td>
                    <el-button :disabled="value.length == 1" @click="deleteItem(index)" type="danger" size="mini">-</el-button>
                    <el-button @click="add()" v-show="(index + 1) == value.length" type="success" size="mini">+</el-button>
                </td>
            </tr>
        </draggable>
    </table>
</template>
<script type="text/babel">
    import draggable from 'vuedraggable'
    export default {
        name: 'ninja_key_pair_options',
        components: { draggable },
        props: ['value'],
        data() {
            return {
                filterArray: []
            }
        },
        methods: {
            deleteItem(index) {
                this.value.splice(index, 1);
            },
            add() {
                this.value.push({
                    label: '',
                    value: ''
                });
            }
        },
        mounted() {

        }
    }
</script>

<style lang="scss">
    table.ninja_filter_table {
        width: 100%;
        text-align: left;
        border-collapse: collapse;
        tr, td, th {
            border: 1px solid #eaeaea;
            padding: 2px 10px;
        }
    }
</style>
