<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;

class AdminMenuHandler
{
    public function add()
    {
        $capability = 'manage_options';

        add_menu_page(
            __('Ninja Tables', 'ninjatables'),
            __('Ninja Tables', 'ninjatables'),
            $capability,
            'ninjatables',
            [$this, 'render'],
            $this->getMenuIcon(),
            6
        );
    }

    public function render()
    {
        $this->enqueueAssets();

        $config = App::getInstance('config');

        $name = $config->get('app.name');

        $slug = $config->get('app.slug');

	    $baseUrl = apply_filters('fluent_connector_base_url', admin_url('admin.php?page=' . $slug . '#/'));

	    $menuItems = [
		    [
			    'key'       => 'dashboard',
			    'label'     => __('Dashboard', 'ninjatables'),
			    'permalink' => $baseUrl
		    ]
	    ];

	    $app = App::getInstance();
	    $assets = $app['url.assets'];

	    App::make('view')->render('admin.menu', [
		    'name'      => $name,
		    'slug'      => $slug,
		    'menuItems' => $menuItems,
		    'baseUrl'   => $baseUrl,
		    'logo'      => $assets . 'images/logo.svg',
	    ]);
    }

    public function enqueueAssets()
    {
        $app = App::getInstance();

        $assets = $app['url.assets'];

        $slug = $app->config->get('app.slug');

        wp_enqueue_style(
            $slug . '_admin_app', $assets . 'admin/admin.css'
        );

        do_action($slug . '_loading_app');

        wp_enqueue_script(
            $slug . '_admin_app',
            $assets . 'admin/app.js',
            array('jquery'),
            '1.0',
            true
        );

	    wp_enqueue_script(
		    $slug . '_global_admin',
		    $assets . 'admin/global_admin.js',
		    array(),
		    '1.0',
		    true
	    );

	    $currentUser = get_user_by('ID', get_current_user_id());

	    wp_localize_script($slug . '_admin_app', 'fluentFrameworkAdmin', [
		    'slug'  => $slug = $app->config->get('app.slug'),
		    'nonce' => wp_create_nonce($slug),
		    'rest'  => $this->getRestInfo($app),
		    'brand_logo' => $this->getMenuIcon(),
		    'asset_url' => $assets,
		    'me'          => [
			    'id'        => $currentUser->ID,
			    'full_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
			    'email'     => $currentUser->user_email
		    ],
	    ]);
    }

    protected function getRestInfo($app)
    {
        $ns = $app->config->get('app.rest_namespace');
        $ver = $app->config->get('app.rest_version');

        return [
            'base_url'  => esc_url_raw(rest_url()),
            'url'       => rest_url($ns . '/' . $ver),
            'nonce'     => wp_create_nonce('wp_rest'),
            'namespace' => $ns,
            'version'   => $ver
        ];
    }

    protected function getMenuIcon()
    {
        return 'dashicons-wordpress-alt';
    }
}

