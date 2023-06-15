<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Support\Arr;
use NinjaTables\Framework\Support\Sanitizer;

class NinjaTableAdminHandler
{
    public function addNinjaTableAdminScript()
    {
        $errorType = get_option('_ninja_suppress_error');
        if ( ! $errorType) {
            $errorType = 'no';
        }
        if ($errorType != 'no'):
            ?>
            <script type="text/javascript">
                // Ninja Tables is supressing the global JS to keep all the JS functions work event other plugins throw error.
                // If You want to disable this please go to Ninja Tables -> Tools -> Global Settings and disable it
                var oldOnError = window.onerror;
                window.onerror = function (message, url, lineNumber) {
                    if (oldOnError) oldOnError.apply(this, arguments);  // Call any previously assigned handler
                    <?php if($errorType == 'log_silently'): ?>
                    console.error(message, [url, "Line#: " + lineNumber]);
                    <?php endif; ?>
                    return true;
                };
            </script>
        <?php
        endif;
    }

    public function adminNotices()
    {
        $this->noticeForProVersion();

        // TODO: We need to uncomment this after one release
//        if (ninjaTablesIsNotice('review_notice')) {
//            if (isset($_GET['page']) && Sanitizer::sanitizeTextField($_GET['page']) == 'ninja_tables') {
//                echo '<div class="nt_review_notice">In love with Ninja Tables?
//                     <a target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">Please leave a 5-star review for us! </a>
//                     It will encourage us to come up with more and more features.
//                     <a target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">Rate Now</a> |
//                     <a href=' . admin_url('admin.php?action=remindMeLater&key=review_notice&ninja_table_admin_nonce=') .wp_create_nonce('ninja_table_admin_nonce'). '>Remind Me Later</a>
//                     <a href=' . admin_url('admin.php?action=remindMeLater&key=review_notice&ninja_table_admin_nonce=') .wp_create_nonce('ninja_table_admin_nonce'). '>
//                        <span class="close-icon dashicons dashicons-no"></span>
//                    </a>
//                 </div>';
//            }
//        }
    }

    public function remindMeLater()
    {
        $key = Sanitizer::sanitizeTextField(Arr::get($_GET, 'key', 'admin_notice'));
        $action = Sanitizer::sanitizeTextField(Arr::get($_GET, 'action', ''));
        $prefix = 'ninja_tables_';

        if ($key && $action === 'remindMeLater') {
            ninjaTablesValidateNonce('ninja_table_admin_nonce');
            setcookie(
               $prefix.$key,
                NINJA_TABLES_VERSION,
                time() + (60 * 60 * 24 * 30)
            );
            wp_redirect(admin_url('admin.php?page=ninja_tables#home'));
        }
    }

    /**
     * Save a flag if the a post/page/cpt have [ninja_tables] shortcode
     *
     * @param int $post_id
     *
     * @return void
     */
    public function saveNinjaTableFlagOnShortCode($post_id)
    {
        if (isset($_POST['post_content'])) {
            $post_content = wp_kses_post($_POST['post_content']);
        } else {
            $post         = get_post($post_id);
            $post_content = $post->post_content;
        }

        $ids = ninjaTablesGetShortCodeIds($post_content);

        if ($ids) {
            update_post_meta($post_id, '_has_ninja_tables', $ids);
        } elseif (get_post_meta($post_id, '_has_ninja_tables', true)) {
            update_post_meta($post_id, '_has_ninja_tables', 0);
        }
    }

    /**
     * Show a notice if the pro version is installed but not updated and version is less than 4.3.5
     *
     * @return void
     */
    public function noticeForProVersion()
    {
        $page = Arr::get($_GET, 'page', '');
        if ($page === 'ninja_tables') {
            if (defined('NINJAPROPLUGIN_VERSION') && version_compare(NINJAPROPLUGIN_VERSION, '5.0.0', '<') && ninjaTablesIsNotice('upgrade_to_pro')) {
                echo '<div class="ntb-version-update-notice">
                <h3>Update Ninja Tables Pro Plugin</h3>
                <p>
                   You are using an outdated version of Ninja Tables Pro. You should update to the latest version; otherwise, some pro features may not work properly.
                    <a href="' . admin_url('plugins.php?s=ninja-tables-pro&plugin_status=all').'">' . __('Please update to the latest version', 'ninja-tables') . '</a>
                </p>
                   <a href=' . admin_url('admin.php?action=remindMeLater&key=upgrade_to_pro&ninja_table_admin_nonce=') .wp_create_nonce('ninja_table_admin_nonce'). '>
                          <span class="close-icon dashicons dashicons-no"></span>
                    </a>
        </div>';
            } else if ( ! defined('NINJAPROPLUGIN_VERSION') && ninjaTablesIsNotice('get_pro')) {
                echo '<div class="ntb-version-update-notice">
                    <p>
                        Get the Pro add-on and unlock the full potential of Ninja Tables! 
                        <a href="https://ninjatables.com/pricing/">Get Pro Now</a>
                    </p>
                    <a href=' . admin_url('admin.php?action=remindMeLater&key=get_pro&ninja_table_admin_nonce=') .wp_create_nonce('ninja_table_admin_nonce'). '>
                          <span class="close-icon dashicons dashicons-no"></span>
                    </a>
                </div>';
            }
        }
    }
}
