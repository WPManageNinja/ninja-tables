<?php

namespace NinjaTables\App\Hooks\Handlers;

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
        if ($this->isNotice()) {
            if (isset($_GET['page']) && Sanitizer::sanitizeTextField($_GET['page']) == 'ninja_tables') {
                echo '<div class="nt_review_notice">In love with Ninja Tables?
                     <a target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">Please leave a 5-star review for us! </a>
                     It will encourage us to come up with more and more features.
                     <a target="_blank" href="https://wordpress.org/support/plugin/ninja-tables/reviews/?filter=5">Rate Now</a> |
                     <a href=' . admin_url('admin.php?action=remindMeLater') . '>Remind Me Later</a>
                     <a href=' . admin_url('admin.php?action=remindMeLater') . '>
                        <span class="close-icon dashicons dashicons-no"></span>
                    </a>
                 </div>';
            }
        }
    }

    public function remindMeLater()
    {
        if (isset($_GET['action']) && Sanitizer::sanitizeTextField($_GET['action']) === 'remindMeLater') {
            setcookie(
                "nt_review_notice",
                NINJA_TABLES_VERSION,
                time() + (60 * 60 * 24 * 30)
            );
            wp_redirect(admin_url('admin.php?page=ninja_tables#home'));
        }
    }

    public function isNotice()
    {
        if (isset($_COOKIE['nt_review_notice'])) {
            $plugin_version = Sanitizer::sanitizeTextField($_COOKIE['nt_review_notice']);
            if ($plugin_version == NINJA_TABLES_VERSION) {
                return false;
            }
        }

        return true;
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
}
