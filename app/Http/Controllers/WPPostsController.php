<?php

namespace NinjaTables\App\Http\Controllers;

use NinjaTables\Framework\Http\Request\Request;
use NinjaTables\Framework\Support\Arr;

class WPPostsController extends Controller
{
    public function getPostTypes(Request $request)
    {
        global $wpdb;

        $postStatuses = ninjaTablesGetPostStatuses();
        $post_fields  = $wpdb->get_col("DESC {$wpdb->prefix}posts"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

        $publicPostTypes = get_post_types(array(
            'public' => true
        ));

        $excludedTypes = $this->app->applyFilters('ninja_table_excluded_post_types', array(
            'ninja-table',
            'revision',
            'nav_menu_item',
            'oembed_cache',
            'user_request',
            'acf-field-group',
            'acf-field'
        ));

        $all_post_types = array_diff(get_post_types(), $excludedTypes);

        $post_types = array(
            'public'  => array(),
            'private' => array()
        );

        foreach ($all_post_types as $type) {
            $taxonomies = get_object_taxonomies($type);
            $taxonomies = array_combine($taxonomies, $taxonomies);

            foreach ($taxonomies as $taxonomy) {
                $taxonomies[$taxonomy] = get_terms([
                    'taxonomy'   => $taxonomy,
                    'hide_empty' => false,
                ]);
            }

            $status = isset($publicPostTypes[$type]) ? 'public' : 'private';

            $post_types[$status][$type] = array(
                'status'     => $status,
                'taxonomies' => $taxonomies,
                'fields'     => array_map(function ($taxonomy) use ($type) {
                    return "{$type}.{$taxonomy}";
                }, array_keys($taxonomies))
            );
        }

        if ($post_types['private']) {
            $post_types = array_merge($post_types['public'], $post_types['private']);
        } else {
            $post_types = $post_types['public'];
        }

        return $this->sendSuccess([
            'data' => compact('post_fields', 'post_types', 'postStatuses')
        ], 200);
    }

    public function getPostTypesAuthor(Request $request)
    {
        $authors = array();

        if (Arr::get($request->all(), 'post_types')) {
            $postTypes = ninja_tables_sanitize_array(Arr::get($request->all(), 'post_types'));
            if ($postTypes) {
                global $wpdb;

                $placeholders = implode(',', array_fill(0, count($postTypes), '%s'));
                $query = $wpdb->prepare("SELECT {$wpdb->prefix}users.ID, {$wpdb->prefix}users.display_name FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}users ON {$wpdb->prefix}users.ID = {$wpdb->prefix}posts.post_author WHERE {$wpdb->prefix}posts.post_type IN ($placeholders) GROUP BY {$wpdb->prefix}posts.post_author", ...$postTypes); // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $authors = $wpdb->get_results($query); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
            }
        }

        return $this->sendSuccess([
            'data' => [
                'authors' => $authors
            ]
        ]);
    }
}
