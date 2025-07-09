<?php

use NinjaTables\Framework\Foundation\Application;
use NinjaTables\App\Hooks\Handlers\ActivationHandler;
use NinjaTables\App\Hooks\Handlers\DeactivationHandler;

return function ($file) {

    $app = new Application($file);

    register_activation_hook($file, function ($netWorkWide) use ($app) {
        ($app->make(ActivationHandler::class))->handle($netWorkWide);
    });

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    add_action('plugins_loaded', function () use ($app) {

        if (defined('NINJAPROPLUGIN_VERSION')) {
            if (!defined('NINJA_TABLE_PRO_FRAMEWORK_VERSION')) {
                // add admin notice for old version of Ninja Tables Pro
                add_action('admin_notices', function () {
                    if (!current_user_can('edit_posts')) {
                        return;
                    }
                    ?>
                    <div class="notice notice-error">
                        <h3 style="margin: 15px 0 0; color: red;"><b>Update Required:</b> Ninja Tables Pro Plugin</h3>
                        <p><?php esc_html_e('Ninja Tables Pro plugin is not compatible with the current version of Ninja Tables. Please update Ninja Tables Pro to the latest version.', 'ninja-tables'); ?></p>
                        <div style="margin-bottom: 20px;">
                            <a class="button button-primary"
                               href="<?php echo admin_url('plugins.php?s=ninja-tables&plugin_status=all') ?>">Update
                                Ninja Tables Pro</a>
                        </div>
                    </div>
                    <?php
                });
                return;
            }
        }

        do_action('ninjatables_loaded', $app);
    });
};
