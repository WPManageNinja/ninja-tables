<?php

namespace NinjaTables\App\Modules\DragAndDrop;

use NinjaTables\App\App;

class InitConfig
{
    protected $app;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    public function getAllInitialData()
    {
        return [
            'components'        => $this->componentConfig(),
            'settings'          => $this->settingConfig(),
            'responsive'        => $this->responsiveConfig(),
            'ready_made_tables' => $this->templateConfig(),
            'table_data'        => $this->getTableData()
        ];
    }

    public function componentConfig()
    {
        return require_once $this->app['path.config'] . 'table-builder/component.php';
    }

    public function settingConfig()
    {
        return require_once $this->app['path.config'] . 'table-builder/setting.php';
    }

    public function responsiveConfig()
    {
        return require_once $this->app['path.config'] . 'table-builder/responsive.php';
    }

    public function templateConfig()
    {
        return require_once $this->app['path.config'] . 'table-builder/templates.php';
    }

    public function getTableData()
    {
        return [
            'id'         => '',
            'table_name' => 'Table Name',
            'data'       => [],
            'table_type' => null,
            'table'      => $this->getOtherTableConfig()
        ];
    }

    public function getOtherTableConfig()
    {
        return [
            'tr'    => 1,
            'tc'    => 1,
            'merge' => [
                'history' => (object)[]
            ]
        ];
    }

    public function makeTableHeader($table_data)
    {
        $headers = [];

        for ($i = 0; $i < (int)$table_data['table']['tc']; $i++) {
            $headers[] = "column_$i";
        }

        return $headers;
    }

    public function makeTableRow($table_data, $importedData = [])
    {
        $rows = [];

        for ($i = 0; $i < $table_data['table']['tr']; $i++) {
            $columns = $table_data['table']['tc'];

            if (count($importedData) > 0) {
                $columns = $importedData[$i];
            }

            $rows[] = [
                'rows'  => $this->makeTableColumn($columns),
                'style' => $this->tableRawStyling(),
            ];

        }

        return $rows;
    }

    public function tableRawStyling()
    {
        return [
            'trId'            => rand(1000000, 9999999),
            'backgroundColor' => '',
            'rowHeight'       => 50
        ];
    }

    public function tableColumnStyling()
    {
        return [
            'tdId'              => rand(10000000, 99999999),
            'backgroundColor'   => '',
            'columnWidth'       => 150,
            'emptyCell'         => '',
            'verticalAlignment' => '',
            'rowspan'           => 1,
            'colspan'           => 1,
            'highlighted'       => [
                'has_pro'     => true,
                'active'      => false,
                'height'      => 10,
                'shadowColor' => '#888',
                'offset_y'    => 10,
                'blur_radius' => 10,
            ]
        ];
    }

    public function makeTableColumn($data)
    {
        $length = $data;

        if (is_array($data)) {
            $length = count($data);
        }

        $columns = [];

        for ($j = 0; $j < $length; $j++) {
            $defaultText = '';

            if (is_array($data)) {
                $data        = array_values($data);
                $defaultText = $data[$j];
            }

            $columns["column_" . $j] = [
                'style'   => $this->tableColumnStyling(),
                'columns' => [
                    [
                        'id'   => rand(100000000, 999999999),
                        'data' => $this->getDefaultPlaceholder($defaultText)
                    ]
                ]
            ];
        };

        return $columns;
    }

    public function getDefaultPlaceholder($value = '')
    {
        $padding = [
            "top"    => 0,
            "bottom" => 0,
            "left"   => 0,
            "right"  => 0,
        ];

        $margin = [
            "top"    => 1,
            "bottom" => 1,
            "left"   => 1,
            "right"  => 1,
        ];

        return [
            "name"    => __("Text", "ninja-tables"),
            "type"    => "text", // (unique)
            "icon"    => "el-icon-edit-outline",
            "has_pro" => false,
            "value"   => $value,
            "style"   => [
                "fontSize"   => 10,
                "color"      => '',
                "alignment"  => 'center',
                "margin"     => $margin,
                "padding"    => $padding,
                "fontWeight" => [],
            ],
        ];
    }
}
