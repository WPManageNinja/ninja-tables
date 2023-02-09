<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Request\Request;
use NinjaTables\Framework\Support\Sanitizer;

class TablesController extends Controller
{

    private $cptName = 'ninja-table';

    public function index(Request $request)
    {
        $perPage = intval($request->per_page) ?: 10;

        $currentPage = intval($request->page);

        $skip = $perPage * ($currentPage - 1);

        $args = array(
            'posts_per_page' => $perPage,
            'offset'         => $skip,
            'orderby'        => Sanitizer::sanitizeTextField($_REQUEST['orderBy']),
            'order'          => Sanitizer::sanitizeTextField($_REQUEST['order']),
            'post_type'      => $this->cptName,
            'post_status'    => 'any',
        );

        if (isset($request->search) && $request->search) {
            $args['s'] = Sanitizer::sanitizeTextField($request->search);
        }

        $tables = get_posts($args);

        foreach ($tables as $table) {
            $provider = get_post_meta($table->ID, '_ninja_tables_data_provider', true);
            if ($provider === 'drag_and_drop') {
                $table->preview_url = site_url('?ninjatable_builder_preview=' . $table->ID);
            } else {
                $table->preview_url = site_url('?ninjatable_preview=' . $table->ID);
            }
            $dataSourceType        = ninja_table_get_data_provider($table->ID);
            $table->dataSourceType = $dataSourceType;
            if ($dataSourceType == 'fluent-form') {
                $fluentFormFormId = get_post_meta($table->ID, '_ninja_tables_data_provider_ff_form_id', true);
                if ($fluentFormFormId) {
                    $table->fluentfrom_url = admin_url('admin.php?page=fluent_forms&route=entries&form_id=' . $fluentFormFormId);
                }
            } elseif ($dataSourceType == 'csv' || $dataSourceType == 'google-csv') {
                $table->remoteURL = get_post_meta($table->ID, '_ninja_tables_data_provider_url', true);
            }
        }

        $tables = $this->app->applyFilters('ninja_tables_get_all_tables', $tables);

        $total    = wp_count_posts($this->cptName);
        $total    = intval($total->publish);
        $lastPage = ceil($total / $perPage);

        $this->json(array(
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $currentPage,
            'last_page'    => ($lastPage) ? $lastPage : 1,
            'data'         => $tables,
        ), 200);
    }
}