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
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tr.footable-header th, <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr th {
        border-color: <?php echo esc_attr($colors['table_color_header_border']); ?> !important;
        }
    <?php endif; ?>

    <?php if(isset($colors['table_color_border']) && $colors['table_color_border']) : ?>
        <?php echo esc_attr($css_prefix); ?>:not(.hide_all_borders) tbody tr td {
        border-color: <?php echo esc_attr($colors['table_color_border']); ?> !important;
        }
    <?php endif; ?>
    <?php echo esc_attr($css_prefix); ?> tbody tr:hover {
    background-color: <?php echo esc_attr($colors['table_color_primary_hover']); ?> !important;
    color: <?php echo esc_attr($colors['table_color_secondary_hover']); ?> !important;
    }
    <?php echo esc_attr($css_prefix); ?> tbody tr:hover td {
    border-color: <?php echo esc_attr($colors['table_color_border_hover']); ?> !important;
    }
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
    <?php foreach ($cellStyles as $cellStyle): ?>
        <?php
        $cell = maybe_unserialize($cellStyle->settings);
        $cellPrefix = $css_prefix.'.ninja_footable.ninja_table_pro tbody tr.nt_row_id_'.$cellStyle->id;
        ?>
        <?php echo esc_attr($cellPrefix)?> {
        <?php if(isset($cell['row_bg'])): ?>background: <?php echo esc_attr($cell['row_bg'].'!important;'); endif; ?>
        <?php if(isset($cell['text_color'])): ?>color: <?php echo esc_attr($cell['text_color'].'!important;'); endif; ?>}
        <?php if($cell && isset($cell['cell']) && is_array($cell['cell'])) : foreach ($cell['cell'] as $cell_key => $values): ?>
            <?php $specCellPrefix = $cellPrefix.' .ninja_clmn_nm_'.$cell_key; ?>
            <?php echo esc_attr($specCellPrefix) ?> {
            <?php foreach ($values as $value_key => $value){ ?>
                <?php if($value): echo esc_attr($value_key); ?> : <?php echo esc_attr($value.';'); endif; ?>
            <?php } ?>
            }
            <?php echo esc_attr($specCellPrefix) ?> > * { color: inherit }
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
<?php  echo ninjaTablesEscCss($custom_css); ?>
