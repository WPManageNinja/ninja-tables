<?php if($fonts):?>
    <?php echo esc_attr($css_prefix); ?>  {
    font-family: <?php echo esc_attr($fonts['table_font_family']);?>;
    font-size: <?php echo esc_attr($fonts['table_font_size']);?>px;
    }
<?php endif;?>

<?php if($colors): ?>

    <?php echo esc_attr($css_prefix);?> tbody tr td span.fooicon-plus:before {
    background-color: <?php echo esc_attr($colors['table_color_secondary']); ?> !important;
    }
    <?php echo esc_attr($css_prefix);?> tbody tr td span.fooicon-minus:before {
    background-color: <?php echo esc_attr($colors['table_color_secondary']); ?> !important;
    }

    <?php echo esc_attr($css_prefix);?> tbody tr:hover td span.fooicon-plus:before {
    background-color: <?php echo esc_attr($colors['table_color_secondary_hover']) ?> !important;
    }
    <?php echo esc_attr($css_prefix);?> tbody tr:hover td span.fooicon-minus:before {
    background-color: <?php echo esc_attr($colors['table_color_secondary_hover']) ?> !important;
    }

    <?php echo esc_attr($css_prefix);?> thead tr.footable-header th span::before {
    background-color: <?php echo esc_attr($colors['table_color_header_secondary'])?> !important;
    }
    <?php echo esc_attr($css_prefix); ?>,
    <?php echo esc_attr($css_prefix); ?> table {
    background-color: <?php echo esc_attr($colors['table_color_primary']); ?> !important;
    color: <?php echo esc_attr($colors['table_color_secondary']); ?> !important;
    border-color: <?php echo esc_attr($colors['table_color_border']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?> thead tr.footable-filtering th {
    background-color: <?php echo esc_attr($colors['table_search_color_primary']); ?> !important;
    color: <?php echo esc_attr($colors['table_search_color_secondary']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) thead tr.footable-filtering th {
    <?php if($colors['table_search_color_border']): ?>
        border : 1px solid <?php echo esc_attr($colors['table_search_color_border']); ?> !important;
    <?php else: ?>
        border : 1px solid transparent !important;
    <?php endif; ?>
    }
    <?php echo esc_attr($css_prefix); ?> .input-group-btn:last-child > .btn:not(:last-child):not(.dropdown-toggle) {
    background-color: <?php echo esc_attr($colors['table_search_color_secondary']); ?> !important;
    color: <?php echo esc_attr($colors['table_search_color_primary']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?> tr.footable-header, <?php echo esc_attr($css_prefix); ?> tr.footable-header th, .colored_table <?php echo esc_attr($css_prefix); ?> table.ninja_table_pro.inverted.table.footable-details tbody tr th {
    background-color: <?php echo esc_attr($colors['table_header_color_primary']); ?> !important;
    color: <?php echo esc_attr($colors['table_color_header_secondary']); ?> !important;
    }
    <?php if($colors['table_color_header_border']) : ?>
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tr.footable-header th,
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr th {
        border-color: <?php echo esc_attr($colors['table_color_header_border']); ?> !important;
        }

        <?php if(!isset($colors['table_color_border']) || !$colors['table_color_border']) : ?>
            <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tr.footable-header th:first-child,
            <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr th:first-child {
            border-left: 1px solid <?php echo esc_attr($colors['table_color_header_border']); ?>;
            }
            <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tr.footable-header th:last-child,
            <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr th:last-child {
            border-right: 1px solid <?php echo esc_attr($colors['table_color_header_border']); ?>;
            }
        <?php endif; ?>
    <?php endif; ?>

    <?php if(isset($colors['table_color_border']) && $colors['table_color_border']) : ?>
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr td {
        border-color: <?php echo esc_attr($colors['table_color_border']); ?> !important;
        }
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr:last-child td {
        border-bottom: 1px solid <?php echo esc_attr($colors['table_color_border']); ?> !important;
        }
    <?php endif; ?>
    <?php echo esc_attr($css_prefix); ?> tbody tr:hover {
    background-color: <?php echo esc_attr($colors['table_color_primary_hover']); ?> !important;
    color: <?php echo esc_attr($colors['table_color_secondary_hover']); ?> !important;
    }
<!--    --><?php //if($colors['table_color_border_hover'] !== '') { ?>
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr td {-->
<!--        border-top: 1px solid transparent;-->
<!--        border-bottom: 1px solid transparent;-->
<!--        }-->
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr td:first-child {-->
<!--        border-left: 1px solid transparent;-->
<!--        }-->
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr td:last-child {-->
<!--        border-right: 1px solid transparent;-->
<!--        }-->
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr:hover td {-->
<!--        border-color: --><?php //echo esc_attr($colors['table_color_border_hover']); ?><!--;-->
<!--        }-->
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr:hover td:first-child {-->
<!--        border-left: 1px solid --><?php //echo esc_attr($colors['table_color_border_hover']); ?><!--;-->
<!--        }-->
<!--        --><?php //echo esc_attr($css_prefix); ?><!-- tbody tr:hover td:last-child {-->
<!--        border-right: 1px solid --><?php //echo esc_attr($colors['table_color_border_hover']); ?><!--;-->
<!--        }-->
<!--    --><?php //} ?>

    <?php if(isset($colors['alternate_color_status']) && $colors['alternate_color_status'] == 'yes'): ?>
        <?php echo esc_attr($css_prefix); ?> tbody tr:nth-child(even) {
        background-color: <?php echo esc_attr($colors['table_alt_color_primary']); ?> !important;
        color: <?php echo esc_attr($colors['table_alt_color_secondary']); ?> !important;
        }
        <?php echo esc_attr($css_prefix); ?> tbody tr:nth-child(odd) {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_primary']); ?> !important;
        color: <?php echo esc_attr($colors['table_alt_2_color_secondary']); ?> !important;
        }
        <?php echo esc_attr($css_prefix); ?> tbody tr:nth-child(even):hover {
        background-color: <?php echo esc_attr($colors['table_alt_color_hover']); ?> !important;
        }
        <?php echo esc_attr($css_prefix); ?> tbody tr:nth-child(odd):hover {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_hover']); ?> !important;
        }

        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(even) td span.fooicon-plus:before {
        background-color: <?php echo esc_attr($colors['table_alt_color_secondary']) ?> !important;
        }
        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(even) td span.fooicon-minus:before {
        background-color: <?php echo esc_attr($colors['table_alt_color_secondary']) ?> !important;
        }

        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(odd) td span.fooicon-plus:before {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_secondary']) ?> !important;
        }
        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(odd) td span.fooicon-minus:before {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_secondary']) ?> !important;
        }

        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(even) tr:hover td span.fooicon-plus:before {
        background-color: <?php echo esc_attr($colors['table_alt_color_secondary']) ?> !important;
        }
        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(even) tr:hover td span.fooicon-minus:before {
        background-color: <?php echo esc_attr($colors['table_alt_color_secondary']) ?> !important;
        }

        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(odd) tr:hover td span.fooicon-plus:before {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_secondary']) ?> !important;
        }
        <?php echo esc_attr($css_prefix);?> tbody tr:nth-child(odd) tr:hover td span.fooicon-minus:before {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_secondary']) ?> !important;
        }
    <?php endif; ?>

    <?php echo esc_attr($css_prefix); ?> tfoot .footable-paging {
    background-color: <?php echo esc_attr($colors['table_footer_bg']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?> tfoot .footable-paging .footable-page.active a {
    background-color: <?php echo esc_attr($colors['table_footer_active']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tfoot tr.footable-paging td {
    border-color: <?php echo esc_attr($colors['table_footer_border']); ?> !important;
    }
<?php endif; ?>
<?php if($cellStyles): ?>
    <?php foreach ($cellStyles as $ninja_tables_cellStyle): ?>
        <?php
        $ninja_tables_cell = maybe_unserialize($ninja_tables_cellStyle->settings);
        $ninja_tables_cellPrefix = $css_prefix.'.ninja_footable.ninja_table_pro tbody tr.nt_row_id_'.$ninja_tables_cellStyle->id;
        ?>
        <?php echo esc_attr($ninja_tables_cellPrefix)?> {
        <?php if(isset($ninja_tables_cell['row_bg'])): ?>background: <?php echo esc_attr($ninja_tables_cell['row_bg'].'!important;'); endif; ?>
        <?php if(isset($ninja_tables_cell['text_color'])): ?>color: <?php echo esc_attr($ninja_tables_cell['text_color'].'!important;'); endif; ?>}
        <?php if($ninja_tables_cell && isset($ninja_tables_cell['cell']) && is_array($ninja_tables_cell['cell'])) : foreach ($ninja_tables_cell['cell'] as $ninja_tables_cell_key => $ninja_tables_values): ?>
            <?php $ninja_tables_specCellPrefix = $ninja_tables_cellPrefix.' .ninja_clmn_nm_'.$ninja_tables_cell_key; ?>
            <?php echo esc_attr($ninja_tables_specCellPrefix) ?> {
            <?php foreach ($ninja_tables_values as $ninja_tables_value_key => $ninja_tables_value){ ?>
                <?php if($ninja_tables_value): echo esc_attr($ninja_tables_value_key); ?> : <?php echo esc_attr($ninja_tables_value.';'); endif; ?>
            <?php } ?>
            }
            <?php echo esc_attr($ninja_tables_specCellPrefix) ?> > * { color: inherit }
        <?php endforeach; endif; // end of if(is_array($cell['cell'])) ?>
    <?php endforeach; ?>
<?php endif; ?>

<?php if($hasStackable): ?>
    <?php echo esc_attr($css_prefix); ?>.ninja_stacked_table > tbody, <?php echo esc_attr($css_prefix); ?>.ninja_stacked_table {
    background: transparent !important;
    }
    <?php if ($colors) : ?>
        <?php echo esc_attr($css_prefix); ?>.ninja_stacked_table .footable-details tbody {
        background-color: <?php echo esc_attr($colors['table_color_primary']); ?> !important;
        color: <?php echo esc_attr($colors['table_color_secondary']); ?> !important;
        border-color: <?php echo esc_attr($colors['table_color_border']); ?> !important;
        }
        <?php echo esc_attr($stackPrefix); ?> thead tr.footable-filtering th {
        background-color: <?php echo esc_attr($colors['table_search_color_primary']); ?> !important;
        color: <?php echo esc_attr($colors['table_search_color_secondary']); ?> !important;
        }
        <?php echo esc_attr($stackPrefix); ?>:not(.hide_all_borders) thead tr.footable-filtering th {
        <?php if($colors['table_search_color_border']): ?>
            border : 1px solid <?php echo esc_attr($colors['table_search_color_border']); ?> !important;
        <?php else: ?>
            border : 1px solid transparent !important;
        <?php endif; ?>
        }
        <?php echo esc_attr($stackPrefix); ?> .input-group-btn:last-child > .btn:not(:last-child):not(.dropdown-toggle) {
        background-color: <?php echo esc_attr($colors['table_search_color_secondary']); ?> !important;
        color: <?php echo esc_attr($colors['table_search_color_primary']); ?> !important;
        }
        <?php echo esc_attr($stackPrefix); ?> tr.footable-header, <?php echo esc_attr($stackPrefix); ?> tr.footable-header th {
        background-color: <?php echo esc_attr($colors['table_header_color_primary']); ?> !important;
        color: <?php echo esc_attr($colors['table_color_header_secondary']); ?> !important;
        }
    <?php endif; ?>
    <?php if(isset($colors['table_color_header_border']) && $colors['table_color_header_border']) : ?>
        <?php echo esc_attr($stackPrefix); ?>:not(.hide_all_borders) tr.footable-header th {
        border-color: <?php echo esc_attr($colors['table_color_header_border']); ?> !important;
        }
    <?php endif; ?>

    <?php if(isset($colors['table_color_border']) && $colors['table_color_border']) : ?>
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr td table {
        border-color: <?php echo esc_attr($colors['table_color_border']); ?> !important;
        }
    <?php endif; ?>
    <?php if(isset($css_prefix['alternate_color_status']) && $css_prefix['alternate_color_status'] == 'yes'): ?>
        <?php echo esc_attr($stackPrefix); ?> tbody tr:nth-child(even) {
        background-color: <?php echo esc_attr($colors['table_alt_color_primary']); ?>;
        color: <?php echo esc_attr($colors['table_alt_color_secondary']); ?>;
        }
        <?php echo esc_attr($stackPrefix); ?> tbody tr:nth-child(odd) {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_primary']); ?>;
        color: <?php echo esc_attr($colors['table_alt_2_color_secondary']); ?>;
        }
        <?php echo esc_attr($stackPrefix); ?> tbody tr:nth-child(even):hover {
        background-color: <?php echo esc_attr($colors['table_alt_color_hover']); ?>;
        }
        <?php echo esc_attr($stackPrefix); ?> tbody tr:nth-child(odd):hover {
        background-color: <?php echo esc_attr($colors['table_alt_2_color_hover']); ?>;
        }
    <?php endif; ?>
<?php endif; ?>
<?php echo ninjaTablesEscCss($custom_css); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
