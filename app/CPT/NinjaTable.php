<?php

namespace NinjaTables\App\CPT;


class NinjaTable
{
    public function registerPostType()
    {
        register_post_type('ninja-table', [
            'public'                => true,
            'show_in_rest'          => true,
            'show_ui'               => false,
            'show_in_nav_menus'     => false,
            'label'                 => 'NinjaTable',
            'description'           => 'Custom post type used in Foo plugin.'
        ]);
    }
}
