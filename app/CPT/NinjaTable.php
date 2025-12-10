<?php

namespace NinjaTables\App\CPT;


class NinjaTable
{
    public function registerPostType()
    {
        $args = array(
            'label'               => __('Ninja Tables', 'ninja-tables'),
            'public'              => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'query_var'           => false,
            'supports'            => array('title'),
            'labels'              => array(
                'name'               => __('Ninja Tables', 'ninja-tables'),
                'singular_name'      => __('Table', 'ninja-tables'),
                'menu_name'          => __('Ninja Tables', 'ninja-tables'),
                'add_new'            => __('Add Table', 'ninja-tables'),
                'add_new_item'       => __('Add New Table', 'ninja-tables'),
                'edit'               => __('Edit', 'ninja-tables'),
                'edit_item'          => __('Edit Table', 'ninja-tables'),
                'new_item'           => __('New Table', 'ninja-tables'),
                'view'               => __('View Table', 'ninja-tables'),
                'view_item'          => __('View Table', 'ninja-tables'),
                'search_items'       => __('Search Table', 'ninja-tables'),
                'not_found'          => __('No Table Found', 'ninja-tables'),
                'not_found_in_trash' => __('No Table Found in Trash', 'ninja-tables'),
                'parent'             => __('Parent Table', 'ninja-tables'),
            ),
        );

        $args = apply_filters('ninja_table_post_type_args', $args);
        register_post_type('ninja-table', $args);
    }
}
