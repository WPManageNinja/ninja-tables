<?php

return [
    "general"        => [
        "name"    => __("General", "ninja-tables"),
        "key"     => 'general', //unique
        "has_pro" => false,
        "options" => [
            "cell_padding"               => [
                "label" => __("Cell Padding", "ninja-tables"),
                "type"  => "slider",
                "value" => 10,
                "min"   => 0,
                "max"   => 50,
            ],
            "table_alignment"            => [
                "label" => __("Table Alignment", "ninja-tables"),
                "type"  => "alignment",
                "value" => 'center',
            ],
            "columns_rows_separate"      => [
                "label"  => __("Separate Columns/Rows", "ninja-tables"),
                "type"   => "switch",
                "value"  => false,
                "childs" => [
                    "space_between_column" => [
                        "label" => __("Space Between Columns", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 3,
                        "min"   => 0,
                        "max"   => 50,
                    ],
                    "space_between_row"    => [
                        "label" => __("Space Between Rows", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 3,
                        "min"   => 0,
                        "max"   => 50,
                    ],
                ],
            ],
            "container_max_width_switch" => [
                "label"  => __("Table Container Max Width", "ninja-tables"),
                "type"   => "switch",
                "value"  => false,
                "childs" => [
                    "container_max_width" => [
                        "label" => __("Table Container Max Width", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 700,
                        "min"   => 100,
                        "max"   => 5000,
                    ],
                ],
            ],
            "cell_min_auto_width"        => [
                "label" => __("Table Cell Min Auto Width", "ninja-tables"),
                "type"  => "slider",
                "value" => 150,
                "min"   => 10,
                "max"   => 500,
            ],
            "container_max_height"       => [
                "label" => __("Table Container Max Height", "ninja-tables"),
                "type"  => "slider",
                "value" => 500,
                "min"   => 100,
                "max"   => 10000,
            ],
        ],
    ],
    "background"     => [
        "name"    => __("Background", "ninja-tables"),
        "key"     => 'background', //unique
        "has_pro" => false,
        "options" => [
            "header_background"   => [
                "label" => __("Header Background", "ninja-tables"),
                "type"  => "color",
                "value" => "#dddddd",
            ],
            "even_row_background" => [
                "label" => __("Even Row Background", "ninja-tables"),
                "type"  => "color",
                "value" => "#dddddd",
            ],
            "odd_row_background"  => [
                "label" => __("Odd Row Background", "ninja-tables"),
                "type"  => "color",
                "value" => "#ffffff",
            ],
        ],
    ],
    "sticky"         => [
        "name"    => __("Sticky", "ninja-tables"),
        "key"     => 'sticky', //unique
        "has_pro" => true,
        "options" => [
            "first_row_sticky"    => [
                "label" => __("First Row Sticky", "ninja-tables"),
                "type"  => "switch",
                "value" => false,
            ],
            "first_column_sticky" => [
                "label" => __("First Column Sticky", "ninja-tables"),
                "type"  => "switch",
                "value" => false,
            ],
        ],
    ],
    "accessibility"  => [
        "name"    => __("Accessibility", "ninja-tables"),
        "key"     => 'accessibility', //unique
        "has_pro" => false,
        "options" => [
            "table_role" => [
                "label" => __("Table Role", "ninja-tables"),
                "type"  => "select",
                "value" => "table",
                "items" => [
                    [
                        "label" => "Table",
                        "value" => "table"
                    ],
                    [
                        "label" => "Presentation",
                        "value" => "presentation"
                    ],
                    [
                        "label" => "List",
                        "value" => "list"
                    ],
                    [
                        "label" => "Row Group",
                        "value" => "rowgroup"
                    ],
                ],
            ],
        ],
    ],
    "border"         => [
        "name"    => __("Border", "ninja-tables"),
        "key"     => 'border', //unique
        "has_pro" => false,
        "options" => [
            "table_border" => [
                "label" => __("Table Border", "ninja-tables"),
                "type"  => "slider",
                "value" => 0,
                "min"   => 0,
                "max"   => 50,
            ],
            "border_color" => [
                "label" => __("Border Color", "ninja-tables"),
                "type"  => "color",
                "value" => "#000000",
            ],
            "inner_border" => [
                "label"  => __("Apply Inner Border", "ninja-tables"),
                "type"   => "switch",
                "value"  => true,
                "childs" => [
                    "header_inner_border" => [
                        "label" => __("Header Inner Border", "ninja-tables"),
                        "type"  => "switch",
                        "value" => true,
                    ],
                    "inner_border_color"  => [
                        "label" => __("Inner Border Color", "ninja-tables"),
                        "type"  => "color",
                        "value" => "#000000",
                    ],
                    "inner_border_size"   => [
                        "label" => __("Inner Border Size", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 1,
                        "min"   => 0,
                        "max"   => 50,
                    ],
                    // "column_border_only" => [
                    //     "label" => __("Column Border Only", "ninja-tables"),
                    //     "type" => "switch",
                    //     "value" => false
                    // ],
                    // "row_border_only" => [
                    //     "label" => __("Row Border Only", "ninja-tables"),
                    //     "type" => "switch",
                    //     "value" => false
                    // ]
                ],
            ],
        ],
    ],
    "global_styling" => [
        "name"    => __("Global Style", "ninja-tables"),
        "key"     => 'global_styling', //unique
        "has_pro" => false,
        "options" => [
            "margin_top"  => [
                "label" => __("Margin Top", "ninja-tables"),
                "type"  => "slider",
                "value" => 0,
                "min"   => 0,
                "max"   => 100,
            ],
            "margin_bottom"  => [
                "label" => __("Margin Bottom", "ninja-tables"),
                "type"  => "slider",
                "value" => 0,
                "min"   => 0,
                "max"   => 100,
            ],
            "font_size"    => [
                "label" => __("Font Size", "ninja-tables"),
                "type"  => "slider",
                "value" => 15,
                "min"   => 12,
                "max"   => 40,
            ],
            "color"        => [
                "label" => __("Color", "ninja-tables"),
                "type"  => "color",
                "value" => "#000001",
            ],
            "font_family"  => [
                "label" => __("Font Family", "ninja-tables"),
                "type"  => "select",
                "value" => "inherit",
                "items" => [
                    [
                        "label" => "Inherit",
                        "value" => "inherit"
                    ],
                    [
                        "label" => "Arial",
                        "value" => "Arial"
                    ],
                    [
                        "label" => "Helvetica",
                        "value" => "Helvetica"
                    ],
                    [
                        "label" => "Comic Sans",
                        "value" => "Comic Sans"
                    ],
                    [
                        "label" => "Courier New",
                        "value" => "Courier New"
                    ],
                    [
                        "label" => "Georgia",
                        "value" => "Georgia"
                    ],
                    [
                        "label" => "Impact",
                        "value" => "Impact"
                    ],
                    [
                        "label" => "Charcoal",
                        "value" => "Charcoal"
                    ],
                    [
                        "label" => "Lucida Grande",
                        "value" => "Lucida Grande"
                    ],
                    [
                        "label" => "Palatino Linotype",
                        "value" => "Palatino Linotype"
                    ],
                    [
                        "label" => "Book Antiqua",
                        "value" => "Book Antiqua"
                    ],
                    [
                        "label" => "Palatino",
                        "value" => "Palatino"
                    ],
                    [
                        "label" => "Tahoma",
                        "value" => "Tahoma"
                    ],
                    [
                        "label" => "Geneva",
                        "value" => "Geneva"
                    ],
                    [
                        "label" => "Times New Roman",
                        "value" => "Times New Roman"
                    ],
                    [
                        "label" => "Verdana",
                        "value" => "Verdana"
                    ],
                    [
                        "label" => "Monaco",
                        "value" => "Monaco"
                    ],

                ],
            ],
        ],
    ],
];
