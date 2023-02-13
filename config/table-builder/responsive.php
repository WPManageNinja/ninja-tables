<?php

return [
    "general"      => [
        "name"    => __("General", "ninja-tables"),
        "key"     => 'general', //unique
        "has_pro" => false,
        "options" => [
            "enable_responsive_table" => [
                "label" => __("Enable Responsive Table", "ninja-tables"),
                "type"  => "switch",
                "value" => true,
            ],
        ],
    ],
    "mode_options" => [
        "name"    => __("Mode Options", "ninja-tables"),
        "key"     => 'mode_options', //unique
        "has_pro" => false,
        "options" => [
            "devices" => [
                "mobile"  => [
                    "name"               => "Mobile",
                    "key"                => "mobile",
                    "disable_breakpoint" => [
                        "label" => __("Disable Breakpoint", "ninja-tables"),
                        "key"   => "disable_breakpoint",
                        "type"  => "switch",
                        "value" => false,
                    ],
                    "cell_direction" => [
                        "label" => __("Cell Stack Direction", "ninja-tables"),
                        "type"  => "select",
                        "value" => "row",
                        "items" => [
                            [
                                "label" => "Row",
                                "value" => "row",
                            ],
                            [
                                "label" => "Column",
                                "value" => "column",
                            ],
                        ],
                    ],
                    "top_row_as_header"  => [
                        "label" => __("Top Row As Header", "ninja-tables"),
                        "key"   => "top_row_as_header",
                        "type"  => "switch",
                        "value" => true,
                    ],
                    "items_per_row"      => [
                        "label" => __("Items Per Header", "ninja-tables"),
                        "key"   => "items_per_header",
                        "type"  => "slider",
                        "value" => 1,
                        "min"   => 1,
                        "max"   => 5,
                    ],
                    "cell_border"        => [
                        "label" => __("Group Separator", "ninja-tables"),
                        "key"   => "mobile_cell_border",
                        "type"  => "slider",
                        "value" => 5,
                        "min"   => 1,
                        "max"   => 10,
                    ],
                ],
                "tablet"  => [
                    "name"               => "Tablet",
                    "key"                => "tablet",
                    "disable_breakpoint" => [
                        "label" => __("Disable Breakpoint", "ninja-tables"),
                        "key"   => "disable_breakpoint",
                        "type"  => "switch",
                        "value" => false,
                    ],
                    "cell_direction" => [
                        "label" => __("Cell Stack Direction", "ninja-tables"),
                        "type"  => "select",
                        "value" => "row",
                        "items" => [
                            [
                                "label" => "Row",
                                "value" => "row",
                            ],
                            [
                                "label" => "Column",
                                "value" => "column",
                            ],
                        ],
                    ],
                    "top_row_as_header"  => [
                        "label" => __("Top Row As Header", "ninja-tables"),
                        "key"   => "top_row_as_header",
                        "type"  => "switch",
                        "value" => true,
                    ],
                    "items_per_row"      => [
                        "label" => __("Items Per Header", "ninja-tables"),
                        "key"   => "items_per_header",
                        "type"  => "slider",
                        "value" => 2,
                        "min"   => 1,
                        "max"   => 5,
                    ],
                    "cell_border"        => [
                        "label" => __("Group Separator", "ninja-tables"),
                        "key"   => "tablet_cell_border",
                        "type"  => "slider",
                        "value" => 5,
                        "min"   => 1,
                        "max"   => 10,
                    ],
                ],
                "desktop" => [
                    "name"              => "Desktop",
                    "key"               => "desktop",
                    "top_row_as_header" => [
                        "label"   => __("Top Row As Header", "ninja-tables"),
                        "key"     => "top_row_as_header",
                        "type"    => "switch",
                        "value"   => false,
                        "disable" => true
                    ],
                    "static_top_row"    => [
                        "label"   => __("Static Top Row", "ninja-tables"),
                        "key"     => "static_top_row",
                        "type"    => "switch",
                        "value"   => false,
                        "disable" => true
                    ],
                ],
            ],
        ]
    ],
    "responsive_settings" => [
        "name"    => __("Responsive Settings", "ninja-tables"),
        "key"     => 'responsive_settings', //unique
        "has_pro" => false,
        "options" => [
            "devices" => [
                "mobile"  => [
                    "name"               => "Mobile",
                    "key"                => "mobile",
                    "mobile_cell_padding"               => [
                        "label" => __("Cell Padding", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 10,
                        "min"   => 0,
                        "max"   => 50,
                    ],
                    "mobile_table_alignment"            => [
                        "label" => __("Table Alignment", "ninja-tables"),
                        "type"  => "alignment",
                        "value" => 'center',
                    ],
                ],
                "tablet"  => [
                    "name"               => "Tablet",
                    "key"                => "tablet",
                    "tablet_cell_padding"               => [
                        "label" => __("Cell Padding", "ninja-tables"),
                        "type"  => "slider",
                        "value" => 10,
                        "min"   => 0,
                        "max"   => 50,
                    ],
                    "tablet_table_alignment"            => [
                        "label" => __("Table Alignment", "ninja-tables"),
                        "type"  => "alignment",
                        "value" => 'center',
                    ],
                ],
            ],
        ],
    ],
];
