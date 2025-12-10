<?php
$ninja_tables_max_width = "";
if (isset($setting['general']['options']['container_max_width_switch']['value']) && $setting['general']['options']['container_max_width_switch']['value'] == 'true') {
    $ninja_tables_max_width      = $setting['general']['options']['container_max_width_switch']['childs']['container_max_width']['value'];
    $ninja_tables_tableAlignment = $setting['general']['options']['table_alignment']['value'];
    $ninja_tables_alignment      = '';
    if ($ninja_tables_tableAlignment === 'left') {
        $ninja_tables_alignment = 'margin-right: auto';
    } else {
        if ($ninja_tables_tableAlignment === 'right') {
            $ninja_tables_alignment = 'margin-left: auto';
        } else {
            if ($ninja_tables_tableAlignment === 'center') {
                $ninja_tables_alignment = 'margin-left: auto; margin-right: auto';
            }
        }
    }
}
$ninja_tables_max_height = "500";
if (isset($setting['general']['options']['container_max_height']['value'])) {
    $ninja_tables_max_height = $setting['general']['options']['container_max_height']['value'];
}
?>

<div class="ntb_table_wrapper <?php echo esc_attr($ntb_instance); ?>"
     id='ninja_table_builder_<?php echo esc_attr($table_id); ?>'
     data-ninja_table_builder_instance="<?php echo esc_attr($ntb_instance); ?>"
     style="
     <?php echo esc_attr("max-height:$ninja_tables_max_height" . "px"); ?>;
     <?php echo esc_attr($ninja_tables_max_width != '' ? "max-width: $ninja_tables_max_width" . "px;" . $ninja_tables_alignment : 'max-width: 1160px'); ?>;">
    <?php
    ninjaTablesPrintSafeVar($ninja_table_builder_html);
    ?>
</div>
<?php
do_action('ninja_tables_drag_and_drop_after_table_print', $table_id);
?>
<?php
if (is_user_logged_in() && ninja_table_admin_role()): ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=ninja_tables#/table_builder_edit_table/' . intval($table_id))); ?>"
       class="ntb_edit_table_class_<?php echo esc_attr($table_id); ?>"><?php esc_html_e('Edit Table', 'ninja-tables'); ?></a>
<?php endif; ?>
