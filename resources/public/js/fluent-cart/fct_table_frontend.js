import NinjaTableFluentCartUtils from './NinjaTableFluentCartUtils';

jQuery(document).ready($ => {
    $(document).on('ninja_table_loaded', (event, $table, settings) => {
        if (settings.provider !== 'wp_fct') return;
        NinjaTableFluentCartUtils.init($table, settings);
    });
});
