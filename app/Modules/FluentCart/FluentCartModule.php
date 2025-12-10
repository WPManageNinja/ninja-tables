<?php

namespace NinjaTables\App\Modules\FluentCart;

use NinjaTables\App\Modules\FluentCart\Handlers\FluentCartHandler;

class FluentCartModule
{
    public function register($app)
    {
        if (!defined('FLUENTCART_VERSION')) {
            return;
        }

        $this->applyHooks($app);
    }

    public function applyHooks($app)
    {
        $app->addAction('wp_ajax_ninja_table_fluentcart_get_options', [FluentCartHandler::class, 'getFluentCartOptions']
        );
        $app->addAction(
            'wp_ajax_ninja_table_fluentcart_create_table',
            [FluentCartHandler::class, 'createFluentCartTable']
        );
        $app->addAction('wp_ajax_ninja_table_fluentcart_update_query', [FluentCartHandler::class, 'saveQuerySettings']);
        $app->addAction(
            'wp_ajax_ninja_table_fluent_cart_get_custom_field_options',
            [FluentCartHandler::class, 'getCustomFieldOptions']
        );
        $app->addAction('ninja_rendering_table_wp_fct', [FluentCartHandler::class, 'addFrontendAsset'], 10, 1);

        $app->addAction('wp_ajax_ninja_table_wp_fct_add_to_cart', [FluentCartHandler::class, 'addToCart']);
        $app->addAction('wp_ajax_nopriv_ninja_table_wp_fct_add_to_cart', [FluentCartHandler::class, 'addToCart']);

        $app->addFilter('ninja_tables_get_table_data_wp_fct', [FluentCartHandler::class, 'getTableData'], 10, 4);
        $app->addFilter('ninja_tables_get_table_wp_fct', [FluentCartHandler::class, 'getTableSettings'], 10, 2);
        $app->addFilter('ninja_tables_fetching_table_rows_wp_fct', [FluentCartHandler::class, 'fetchData'], 10, 6);
    }
}
