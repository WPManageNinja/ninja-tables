<?php
$ninja_tables_table_columns = array_reverse($table_columns);
$ninja_tables_header_row = '';
$ninja_tables_counter = 1;
$ninja_tables_hasImageFunction = function_exists('nt_parse_image_column');
?>
<thead>
<tr class="footable-header">
    <?php foreach ($ninja_tables_table_columns as $ninja_tables_index => $ninja_tables_table_column) : ?>
        <?php
        if (wp_strip_all_tags($ninja_tables_table_column['title']) == '#colspan#') {
            $ninja_tables_header_row = '<td class="ninja_temp_cell"></td>' . $ninja_tables_header_row;
            $ninja_tables_counter++;
            continue;
        }
        $ninja_tables_colspan = '';
        if ($ninja_tables_counter > 1) {
            $ninja_tables_colspan = 'colspan="' . $ninja_tables_counter . '"';
        }
        $ninja_tables_header_row = '<th scope="col" ' . $ninja_tables_colspan . ' class="' . implode(' ', (array)$ninja_tables_table_column['classes']) . ' ' . $ninja_tables_table_column['breakpoints'] . '">' . do_shortcode($ninja_tables_table_column['title']) . '</th>' . $ninja_tables_header_row;
        ?>
        <?php $ninja_tables_counter = 1; endforeach; ?>
    <?php ninjaTablesPrintSafeVar($ninja_tables_header_row); // the $header_row html attributes from admins are already escaped and sanitized ?>
</tr>
</thead>
<tbody>

<?php
if ($table_rows && count($ninja_tables_table_columns)):
    $ninja_tables_columnLength = count($ninja_tables_table_columns) - 1;
    foreach ($table_rows as $ninja_tables_row_index => $ninja_tables_table_row) :
        $ninja_tables_row = '';
        $ninja_tables_rowId = '';
        if (isset($ninja_tables_table_row['___id___'])) {
            $ninja_tables_rowId = $ninja_tables_table_row['___id___'];
        } else {
            $ninja_tables_rowId = $ninja_tables_row_index;
        }

        $ninja_tables_row_class = 'ninja_table_row_' . $ninja_tables_row_index;
        $ninja_tables_row_class .= ' nt_row_id_' . $ninja_tables_rowId;
        ?>
        <tr data-row_id="<?php echo esc_attr($ninja_tables_rowId); ?>" class="<?php echo esc_attr($ninja_tables_row_class); ?>">
            <?php
            $ninja_tables_colSpanCounter = 1; // Make the colspan counter 1 at first
            foreach ($ninja_tables_table_columns as $ninja_tables_index => $ninja_tables_table_column) {
                $ninja_tables_column_value = (isset($ninja_tables_table_row[$ninja_tables_table_column['name']]) ? $ninja_tables_table_row[$ninja_tables_table_column['name']] : null);
                $ninja_tables_columnValueDataAtts = '';
                $ninja_tables_columnType = (isset($ninja_tables_table_column['original']['data_type']) ? $ninja_tables_table_column['original']['data_type'] : null);
                if (is_array($ninja_tables_column_value)) {
                    if ($ninja_tables_columnType == 'image') {
                        $ninja_tables_columnValueDataAtts = json_encode($ninja_tables_column_value);
                        if ($ninja_tables_hasImageFunction) {
                            $ninja_tables_column_value = nt_parse_image_column($ninja_tables_column_value, $ninja_tables_table_column);
                        } else {
                            $ninja_tables_column_value = '';
                        }
                    } else {
                        $ninja_tables_columnValueDataAtts = json_encode($ninja_tables_column_value);
                        $ninja_tables_column_value = implode(', ', $ninja_tables_column_value);
                        $ninja_tables_column_value = do_shortcode($ninja_tables_column_value);
                    }
                } else if ($ninja_tables_columnType == 'button') {
                    if ($ninja_tables_hasImageFunction) {
                        $ninja_tables_column_value = nt_parse_button_column($ninja_tables_column_value, $ninja_tables_table_column);
                    }
                } else if(is_string($ninja_tables_column_value)) {
                    $ninja_tables_column_value = do_shortcode($ninja_tables_column_value);
                }
                $ninja_tables_colspan = false;
                if ($ninja_tables_index != $ninja_tables_columnLength) {
                    if ($ninja_tables_column_value && wp_strip_all_tags($ninja_tables_column_value) == '#colspan#') {
                        $ninja_tables_row = '<td class="ninja_temp_cell" data-colspan="#colspan#"></td>' . $ninja_tables_row;
                        $ninja_tables_colSpanCounter = $ninja_tables_colSpanCounter + 1;
                        // if we get #colspan# value then we are increasing colspan counter by 1 and adding a temp column
                        continue;
                    }
                }

                if ($ninja_tables_colSpanCounter > 1) {
                    $ninja_tables_colspan = ' colspan="' . $ninja_tables_colSpanCounter . '"';
                    // if colspan counter is greater than 1 then we are adding the colspan into the dom
                }

                if ($ninja_tables_columnValueDataAtts) {
                    $ninja_tables_row = '<td' . $ninja_tables_colspan . ' data-json_values=' . $ninja_tables_columnValueDataAtts . '>' . $ninja_tables_column_value . '</td>' . $ninja_tables_row;
                } else {
                    $ninja_tables_row = '<td' . $ninja_tables_colspan . '>' . $ninja_tables_column_value . '</td>' . $ninja_tables_row;
                }

                $ninja_tables_colSpanCounter = 1;
                // we are reseting the colspan counter value here because the colspan is done for this iteration
            }
            ninjaTablesPrintSafeVar($ninja_tables_row); //the $row html attributes from admins are already escaped and sanitized
            ?>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody><!--ninja_tobody_rendering_done-->
