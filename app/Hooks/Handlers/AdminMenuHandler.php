<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\App\App;
use NinjaTables\App\Modules\I18nStrings;
use NinjaTables\App\Utils\Vite;

class AdminMenuHandler
{
    protected function getViteAsset($entry) 
    {
        $devServer = 'http://localhost:5173';
        $isDevelopment = defined('NINJA_TABLES_DEVELOPMENT') && NINJA_TABLES_DEVELOPMENT;
        
        if ($isDevelopment) {
            return $devServer . '/' . $entry;
        }

        static $manifest = null;
        if ($manifest === null) {
            $manifestPath = NINJA_TABLES_DIR_PATH . 'assets/manifest.json';
            $manifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
        }

        $entry = ltrim($entry, '/');
        return isset($manifest[$entry]) 
            ? NINJA_TABLES_DIR_URL . 'assets/' . $manifest[$entry]['file'] 
            : '';
    }

    public function add()
    {
        global $submenu;
        $capability = ninja_table_admin_role();

        if (!$capability) {
            return;
        }
        // Top-level page
        $menuName = __('Ninja Tables', 'ninja-tables');
        if (defined('NINJATABLESPRO')) {
            $menuName .= ' Pro';
        }

        add_menu_page(
            $menuName,
            $menuName,
            $capability,
            'ninja_tables',
            [$this, 'render'],
            $this->getMenuIcon(),
            6
        );

        $submenu['ninja_tables']['all_tables'] = array(
            __('Tables', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/',
            '',
            'ninja_tables_all_tables'
        );


        $submenu['ninja_tables']['import'] = array(
            __('Import', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/tools',
            '',
            'ninja_table_import_menu'
        );

        $submenu['ninja_tables']['tools'] = array(
            __('Tools', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/tools',
            '',
            'ninja_table_tools_menu'
        );

        if (!defined('NINJA_CHARTS_VERSION')) {
            $submenu['ninja_tables']['ninja_charts'] = array(
                __('Charts', 'ninja-tables'),
                $capability,
                'admin.php?page=ninja_tables#/charts'
            );
        } else {
            $submenu['ninja_tables']['ninja_charts'] = array(
                __('Charts', 'ninja-tables'),
                $capability,
                'admin.php?page=ninja-charts#/chart-list'
            );

            $submenu['ninja_tables']['add_chart'] = array(
                __('Add Chart', 'ninja-tables'),
                $capability,
                'admin.php?page=ninja-charts#/add-chart',
            );
        }

        if (!defined('NINJATABLESPRO')) {
            $getPro = __('Get Pro', 'ninja-tables');
            $submenu['ninja_tables']['upgrade_pro'] = array(
                '<span style="color:#f39c12;">' . $getPro . '</span>',
                $capability,
                'https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade_menu',
                '',
                'ninja_table_upgrade_menu'
            );
        } elseif (defined('NINJATABLESPRO_SORTABLE')) {
            $license = get_option('_ninjatables_pro_license_status');
            if ($license != 'valid' && is_multisite()) {
                $license = get_network_option(get_main_network_id(), '_ninjatables_pro_license_status');
            }

            if ($license != 'valid') {
                $text = 'Activate License';
                if ($license == 'expired') {
                    $text = 'Renew License';
                }

                $submenu['ninja_tables']['activate_license'] = array(
                    '<span style="color:#f39c12;">' . $text . '</span>',
                    $capability,
                    'admin.php?page=ninja_tables#/tools/licensing',
                    '',
                    'ninja_table_license_menu'
                );
            }
        }

        $submenu['ninja_tables']['help'] = array(
            __('Help', 'ninja-tables'),
            $capability,
            'admin.php?page=ninja_tables#/help',
            '',
            'ninja_tables_help'
        );

        $submenu = apply_filters('ninja_tables/add_submenu', $submenu, $capability);
    }

    public function render()
    {
        
        echo '<div class="wrap ninja-tables-wrapper">';
        echo '  <div id="data-tables-app"></div>';
        echo '</div>';
    }

    public function enqueueAssets()
    {
        if (isset($_GET['page']) && $_GET['page'] == 'ninja_tables') {
            $this->enqueueStyles();
            $this->enqueueScripts();
        }
    }

    protected function getRestInfo($app)
    {
        $ns  = $app->config->get('app.rest_namespace');
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
        return 'data:image/svg+xml;base64,'
               . base64_encode(
                   '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
              viewBox="0 0 80 80" xml:space="preserve">
                  <g>
                     <g>
                        <polyline points="0.6,51.2 18.4,51.2 18.4,38.6 0.6,38.6" fill="#ffffff" />
                        <path d="M0.6,63.1c0,1,0,1.9,0.2,2.8h17.6V53.5H0.6" fill="#ffffff"/>
                        <path d="M0.6,22.6" fill="#ffffff"/>
                        <path d="M0.6,21.5h78.8v-4.7c0-9-7.5-16.2-16.7-16.2H20.4h-3C8.1,0.5,0.6,7.8,0.6,16.7" fill="#ffffff"/>
                        <polyline points="0.6,36.3 18.4,36.3 18.4,23.8 0.6,23.8" fill="#ffffff"/>
                        <rect x="20.6" y="38.6" width="58.8" height="12.5" fill="#ffffff"/>
                        <rect x="20.6" y="23.8" width="58.8" height="12.4" fill="#ffffff"/>
                        <path d="M79.3,65.9c0.1-1.1,0.1-1.8,0.1-2.7v-9.7H20.6v12.4L79.3,65.9" fill="#ffffff"/>
                     </g>
                        <path d="M18.4,79.3L18.4,79.3v-11H1.5v0.1c2.2,6.4,8.5,11,15.9,11L18.4,79.3L18.4,79.3z" fill="#ffffff"/>
                        <path d="M78.6,68.3h-58v11v0.1h42.1C70.1,79.4,76.4,74.8,78.6,68.3C78.6,68.4,78.6,68.4,78.6,68.3" fill="#ffffff"/>
                  </g>
                </svg>'
               );
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueStyles()
    {
        $app = App::getInstance();
        $slug = $app->config->get('app.slug');

        // Enqueue Element Plus styles
        // wp_enqueue_style(
        //     $slug . '_element_plus',
        //     'https://unpkg.com/element-plus/dist/index.css',
        //     [],
        //     NINJA_TABLES_VERSION
        // );

        // Enqueue main admin styles
        Vite::enqueueStyle(
            $slug . '_admin',
            'admin/css/ninja-tables-admin.scss'
        );

        // Enqueue vendor styles with RTL support
        if (is_rtl()) {
            wp_enqueue_style(
                $slug . '_admin_vendor',
                NINJA_TABLES_DIR_URL . 'assets/css/ninja-tables-vendor-rtl.css',
                [],
                NINJA_TABLES_VERSION
            );
        } else {
            Vite::enqueueStyle(
                $slug . '_admin_vendor',
                'admin/css/vendor.scss'
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueueScripts()
    {
        $app = App::getInstance();
        $slug = $app->config->get('app.slug');
        $assets = Vite::getAssetsUrl();
    
        // Get current user
        $currentUser = wp_get_current_user();
        
        // Initialize variables
        $isAdmin = current_user_can('manage_options') ? 'yes' : 'no';
        $isInstalled = defined('FLUENTFORM') || defined('NINJATABLESPRO');
        $hasFluentFrom = defined('FLUENTFORM_VERSION');
        $isFluentFromUpdated = false;
        
        // Check FluentForm version
        if ($hasFluentFrom) {
            $isFluentFromUpdated = version_compare(FLUENTFORM_VERSION, '1.7.4', '>=');
        }
        
        // Initialize review and lead status
        $leadStatus = false;
        $reviewOptinStatus = false;
        $dismissed = false;
        
        // Check for plugin suggestion dismissal
        $dismissedTime = get_option('_ninja_tables_plugin_suggest_dismiss');
        if ($dismissedTime) {
            $dismissed = (time() - intval($dismissedTime)) < 518400;
        } else {
            $dismissed = true;
            update_option('_ninja_tables_plugin_suggest_dismiss', time() - 345600);
        }
        
        // FluentForm URL
        $fluentUrl = admin_url('plugin-install.php?s=FluentForm&tab=search&type=term');

        // Add admin data
        wp_register_script('ninja-tables-data', '', [], '', true);
        wp_enqueue_script('ninja-tables-data');
        wp_add_inline_script('ninja-tables-data', 'window.ninja_table_admin = ' . wp_json_encode([
            'i18n' => (new I18nStrings())->getStrings(),
            'rest' => $this->getRestInfo($app),
            'asset_url' => Vite::getAssetsUrl(),
            // 'pro_enabled' => defined('NINJATABLESPRO'),
            // 'integrity' => $this->getIntegrity(),
            'nonce' => wp_create_nonce($slug),
            'published_tables' => $this->getPublishedTablesCount(),
            'slug'                     => $slug,
            'brand_logo'               => $this->getMenuIcon(),
            'fluent_wp_url'            => 'https://wordpress.org/plugins/fluentform/',
            'hasPro'                   => defined('NINJATABLESPRO'),
            'fluent_form_icon'         => function_exists('getNinjaFluentFormMenuIcon') ? getNinjaFluentFormMenuIcon(
                ) : '',
            'hasAdvancedFilters'       => class_exists('NinjaTablesPro\App\Hooks\Handlers\CustomFilterHandler'),
            'hasSortable'              => defined('NINJATABLESPRO_SORTABLE'),
            'upgradeGuide'             => 'https://wpmanageninja.com/r/docs/ninja-tables/how-to-install-and-upgrade/#upgrade',
            'hasValidLicense'          => get_option('_ninjatables_pro_license_status'),
            'activated_features'       => $app->applyFilters('ninja_table_activated_features', array(
                'default_tables'    => true,
                'fluentform_tables' => true
            )),
            'nt_integrity'             => $this->getIntegrity(),
            'admin_notices'            => $app->applyFilters('ninja_dashboard_notices', []),
            'prefered_thumb'           => $app->applyFilters('ninja_table_prefered_thumb', 'medium'),
            'has_woocommerce'          => defined('WC_PLUGIN_FILE'),
            'license_status'           => get_option('_ninjatables_pro_license_status'),
            'ninja_charts_url'         => defined('NINJA_CHARTS_VERSION') ? self_admin_url(
                'admin.php?page=ninja-charts#/chart-list'
            ) : null,
            'ninja_table_admin_nonce'  => wp_create_nonce('ninja_table_admin_nonce'),
            'ninja_tables_pro_url'     => defined('NINJATABLESPRO') ? NINJAPROPLUGIN_URL : null,
            'me'                       => [
                'id'        => $currentUser->ID,
                'full_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'email'     => $currentUser->user_email
            ],
            'img_url'                  => $assets . "img/",
            'fluentform_url'           => $fluentUrl,
            'dismissed'                => $dismissed,
            'show_lead_pop_up'         => $leadStatus,
            'show_review_dialog'       => $reviewOptinStatus,
            'current_user_name'        => $currentUser->display_name,
            'isInstalled'              => $isInstalled,
            
            'hasFluentForm'            => $hasFluentFrom,
            'isFluentFormUpdated'      => $isFluentFromUpdated,

            'ace_path_url'             => $assets . "libs/ace",
        
            'preview_required_scripts' => array(
                $assets . "css/ninjatables-public.css",
                $assets . "libs/footable/js/footable.min.js",
                $assets . "libs/moment/moment.min.js",
                $assets . "js/ninja-tables-footable.js",
            ),

            'has_sql_permission'       => $app->applyFilters('ninja_table_sql_permission', $isAdmin),
        ]));

        // Enqueue main app script
        Vite::enqueueScript(
            $slug,
            'admin/main.js',
            ['ninja-tables-data'],
            NINJA_TABLES_VERSION,
            true
        );
        $this->handleScriptConflicts();
    }

    private function handleScriptConflicts()
    {
        // Remove conflicting scripts
        add_action('admin_print_scripts', function () {
            wp_dequeue_script('vuejs');
            wp_dequeue_script('vue');
            wp_deregister_script('elementor-admin-app');
        });

        // No conflict mode
        add_action('wp_print_scripts', function () {
            if (!is_admin() || apply_filters('ninja_table_skip_no_confict', false)) {
                return;
            }

            global $wp_scripts;
            $pluginUrl = plugins_url();
            foreach ($wp_scripts->queue as $script) {
                $src = $wp_scripts->registered[$script]->src;
                if (strpos($src, $pluginUrl) !== false && !strpos($src, 'ninja-tables') !== false) {
                    wp_dequeue_script($wp_scripts->registered[$script]->handle);
                }
            }
        }, 1);
    }

    private function getPublishedTablesCount()
    {
        $tableCount = wp_count_posts('ninja-table');
        return property_exists($tableCount, 'publish') ? intval($tableCount->publish) : 0;
    }

    private function getIntegrity()
    {
        if (defined('NINJATABLESPRO')) {
            if (is_multisite()) {
                return 'valid';
            }
            $status = get_option('_ninjatables_pro_license_status');
            if (is_multisite() && $status != 'valid') {
                $status = get_network_option(get_main_network_id(), '_ninjatables_pro_license_status');
            }
            if ($status == 'valid') {
                $key = get_option('_ninjatables_pro_license_key');
                if (is_multisite()) {
                    $key = get_network_option(get_main_network_id(), '_ninjatables_pro_license_key');
                }
                $length = strlen($key);
                if ($length < 20) {
                    return apply_filters('ninja_table_integrity', 'nope');
                }
            }
        }

        return apply_filters('ninja_table_integrity', 'valid');
    }

    public function getInlineScript()
    {
        return "
        function isLodash () {
        
        let isLodash = false;
    
        // If _ is defined and the function _.forEach exists then we know underscore OR lodash are in place
        if ( 'undefined' != typeof( _ ) && 'function' == typeof( _.forEach ) ) {
    
            // A small sample of some of the functions that exist in lodash but not underscore
            const funcs = [ 'get', 'set', 'at', 'cloneDeep' ];
    
            // Simplest if assume exists to start
            isLodash  = true;
    
            funcs.forEach( function ( func ) {
                // If just one of the functions do not exist, then not lodash
                isLodash = ( 'function' != typeof( _[ func ] ) ) ? false : isLodash;
            } );
        }
    
        if ( isLodash ) {
            // We know that lodash is loaded in the _ variable
            return true;
        } else {
            // We know that lodash is NOT loaded
            return false;
        }
    };
    
    if ( isLodash() ) {
        _.noConflict();
    }
    ";
    }
}

