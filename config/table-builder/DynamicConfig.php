<?php

class DynamicConfig
{
    public static function getTableDataInfo($data_from_db, $updated_column_properties, $updated_row_properties)
    {
        $updatedDataRow = self::getRowStyle($data_from_db, $updated_row_properties); //row style updated

        return self::getColumnStyle($updatedDataRow, $updated_column_properties); //column style updated
    }

    public static function getColumnStyle($data, $updated_column_properties)
    {
        $columnStyleCount = count($updated_column_properties);
        foreach ($data as &$rows) {
            foreach ($rows['rows'] as &$column) {
                if (count($column['style']) === $columnStyleCount) {
                    return $data;
                }
                $column['style'] = array_merge($updated_column_properties, $column['style']);
            }
        }

        return $data;

    }

    public static function getRowStyle($data_from_db, $updated_row_properties)
    {
        $rowStyleCount = count($updated_row_properties);
        foreach ($data_from_db as &$rows) {
            if (count($rows['style']) === $rowStyleCount) {
                return $data_from_db;
            }
            $rows['style'] = array_merge($updated_row_properties, $rows['style']);
        }

        return $data_from_db;
    }

    public static function getSetting($dynamic_setting, $static_config)
    {
        $general       = $dynamic_setting['general']['options'];
        $background    = $dynamic_setting['background']['options'];
        $sticky        = $dynamic_setting['sticky']['options'];
        $accessibility = $dynamic_setting['accessibility']['options'];
        $border        = $dynamic_setting['border']['options'];
        $global_style  = $dynamic_setting['global_styling']['options'];

        $static_config['general']['options']['cell_padding']['value']                                                = $general['cell_padding']['value'];
        $static_config['general']['options']['table_alignment']['value']                                             = $general['table_alignment']['value'];
        $static_config['general']['options']['cell_min_auto_width']['value']                                         = $general['cell_min_auto_width']['value'];
        $static_config['general']['options']['container_max_height']['value']                                        = $general['container_max_height']['value'];
        $static_config['general']['options']['columns_rows_separate']['value']                                       = $general['columns_rows_separate']['value'];
        $static_config['general']['options']['columns_rows_separate']['childs']['space_between_column']['value']     = $general['columns_rows_separate']['childs']['space_between_column']['value'];
        $static_config['general']['options']['columns_rows_separate']['childs']['space_between_row']['value']        = $general['columns_rows_separate']['childs']['space_between_row']['value'];
        $static_config['general']['options']['container_max_width_switch']['value']                                  = $general['container_max_width_switch']['value'];
        $static_config['general']['options']['container_max_width_switch']['childs']['container_max_width']['value'] = $general['container_max_width_switch']['childs']['container_max_width']['value'];

        $static_config['background']['options']['header_background']['value']   = $background['header_background']['value'];
        $static_config['background']['options']['even_row_background']['value'] = $background['even_row_background']['value'];
        $static_config['background']['options']['odd_row_background']['value']  = $background['odd_row_background']['value'];

        $static_config['sticky']['options']['first_row_sticky']['value']    = $sticky['first_row_sticky']['value'];
        $static_config['sticky']['options']['first_column_sticky']['value'] = $sticky['first_column_sticky']['value'];

        $static_config['accessibility']['options']['table_role']['value'] = $accessibility['table_role']['value'];


        $static_config['border']['options']['table_border']['value']                                  = $border['table_border']['value'];
        $static_config['border']['options']['border_color']['value']                                  = $border['border_color']['value'];
        $static_config['border']['options']['inner_border']['value']                                  = $border['inner_border']['value'];
        $static_config['border']['options']['inner_border']['childs']['header_inner_border']['value'] = $border['inner_border']['childs']['header_inner_border']['value'];
        $static_config['border']['options']['inner_border']['childs']['inner_border_color']['value']  = $border['inner_border']['childs']['inner_border_color']['value'];
        $static_config['border']['options']['inner_border']['childs']['inner_border_size']['value']   = $border['inner_border']['childs']['inner_border_size']['value'];

        $static_config['global_styling']['options']['margin_top']['value']    = $global_style['margin_top']['value'];
        $static_config['global_styling']['options']['margin_bottom']['value'] = isset($global_style['margin_bottom']['value']) ? $global_style['margin_bottom']['value'] : 0;
        $static_config['global_styling']['options']['font_size']['value']     = $global_style['font_size']['value'];
        $static_config['global_styling']['options']['color']['value']         = $global_style['color']['value'];
        $static_config['global_styling']['options']['font_family']['value']   = $global_style['font_family']['value'];

        return $static_config;
    }

    public static function getResponsive($dynamic_responsive, $static_config)
    {
        $general        = $dynamic_responsive['general']['options'];
        $mobile         = $dynamic_responsive['mode_options']['options']['devices']['mobile'];
        $tablet         = $dynamic_responsive['mode_options']['options']['devices']['tablet'];
        $mobileSettings = isset($dynamic_responsive['responsive_settings']['options']['devices']['mobile']) ? $dynamic_responsive['responsive_settings']['options']['devices']['mobile'] : null;
        $tabletSettings = isset($dynamic_responsive['responsive_settings']['options']['devices']['tablet']) ? $dynamic_responsive['responsive_settings']['options']['devices']['tablet'] : null;

        $static_config['general']['options']['enable_responsive_table']['value'] = $general['enable_responsive_table']['value'];

        $static_config['mode_options']['options']['devices']['mobile']['disable_breakpoint']['value'] = $mobile['disable_breakpoint']['value'];
        $static_config['mode_options']['options']['devices']['mobile']['top_row_as_header']['value']  = $mobile['top_row_as_header']['value'];
        $static_config['mode_options']['options']['devices']['mobile']['items_per_row']['value']      = isset($mobile['items_per_row']['value']) ? $mobile['items_per_row']['value'] : 1;
        $static_config['mode_options']['options']['devices']['mobile']['cell_border']['value']        = isset($mobile['cell_border']['value']) ? $mobile['cell_border']['value'] : 5;
        $static_config['mode_options']['options']['devices']['mobile']['cell_direction']['value']     = isset($mobile['cell_direction']['value']) ? $mobile['cell_direction']['value'] : 'row';

        $static_config['mode_options']['options']['devices']['tablet']['disable_breakpoint']['value'] = $tablet['disable_breakpoint']['value'];
        $static_config['mode_options']['options']['devices']['tablet']['top_row_as_header']['value']  = $tablet['top_row_as_header']['value'];
        $static_config['mode_options']['options']['devices']['tablet']['items_per_row']['value']      = isset($tablet['items_per_row']['value)']) ? $tablet['items_per_row']['value'] : 2;
        $static_config['mode_options']['options']['devices']['tablet']['cell_border']['value']        = isset($tablet['cell_border']['value']) ? $tablet['cell_border']['value'] : 5;
        $static_config['mode_options']['options']['devices']['tablet']['cell_direction']['value']     = isset($tablet['cell_direction']['value']) ? $tablet['cell_direction']['value'] : 'row';

        $static_config['responsive_settings']['options']['devices']['mobile']['mobile_table_alignment']['value'] = isset($mobileSettings['mobile_table_alignment']['value']) ? $mobileSettings['mobile_table_alignment']['value'] : 'center';
        $static_config['responsive_settings']['options']['devices']['mobile']['mobile_cell_padding']['value']    = isset($mobileSettings['mobile_cell_padding']['value']) ? $mobileSettings['mobile_cell_padding']['value'] : 10;

        $static_config['responsive_settings']['options']['devices']['tablet']['tablet_table_alignment']['value'] = isset($tabletSettings['tablet_table_alignment']['value']) ? $tabletSettings['tablet_table_alignment']['value'] : 'center';
        $static_config['responsive_settings']['options']['devices']['tablet']['tablet_cell_padding']['value']    = isset($tabletSettings['tablet_cell_padding']['value']) ? $tabletSettings['tablet_cell_padding']['value'] : 10;

        return $static_config;
    }
}
