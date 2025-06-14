<?php

namespace NinjaTables\App\Hooks\Handlers;

use NinjaTables\Framework\Support\Sanitizer;
use NinjaTables\Framework\Support\Arr;

class NinjaTableAdminHandler
{
    public function addNinjaTableAdminScript()
    {
        $errorType = get_option('_ninja_suppress_error');
        if (!$errorType) {
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
}
