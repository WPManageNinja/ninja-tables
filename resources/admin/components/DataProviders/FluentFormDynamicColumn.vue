<template>
    <div v-loading="loading" class="fluent_form_dynamic_field">
        <h4>{{ $t('Dynamic Fields Settings') }}</h4>
        <hr />
    </div>
</template>

<script>
    export default {
        name: "FluentFormDynamicColumn",
        props: ['column'],
        data() {
            return {
                loading: false,
                fluent_form_data_types: []
            };
        },
        computed: {
            // ...
        },
        methods: {
            // ...
        },
        mounted() {
            // ...
        }
    };
</script>
