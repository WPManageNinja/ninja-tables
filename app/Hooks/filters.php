<?php

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app NinjaTables\Framework\Foundation\Application
 */

$app->addFilter("plugin_action_links_" . NINJA_TABLES_BASENAME, function ($links) {

    if ( ! defined('NINJATABLESPRO')) {
        $links[] = '<a style="color: green; font-weight: bold;" target="_blank" href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/">Go Pro</a>';
    }

    $links[] = '<a href="' . admin_url('admin.php?page=ninja_tables#/') . '">' . __('All Tables',
            'ninja-tables') . '</a>';

    return $links;
});

$app->addFilter('upload_mimes', function ($file_types) {

    $new_filetypes        = [];
    $new_filetypes['svg'] = 'image/svg+xml';
    $file_types           = array_merge($file_types, $new_filetypes);

    return $file_types;
}, 10, 1);
