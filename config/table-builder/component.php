<?php

$default_margin_padding = [
    "top"    => 0,
    "bottom" => 0,
    "left"   => 0,
    "right"  => 0,
];

return [
    "general" => [
        "name"   => __("General", "ninja-tables"),
        "key"    => "general", // unique
        "fields" => [
            [
                "name"    => __("Text", "ninja-tables"),
                "type"    => "text", // (unique)
                "icon"    => "el-icon-edit-outline",
                "has_pro" => false,
                "value"   => '',
                "style"   => [
                    "fontSize"   => 10,
                    "color"      => '',
                    "alignment"  => 'center',
                    "margin"     => $default_margin_padding,
                    "padding"    => $default_margin_padding,
                    "fontWeight" => [],
                ],
            ],
            [
                "name"    => __("Button", "ninja-tables"),
                "type"    => "button", // (unique)
                "icon"    => "el-icon-bank-card",
                "has_pro" => false,
                "value"   => "Button Text",
                "style"   => [
                    "fontSize"             => 10,
                    "color"                => '',
                    "backgroundColor"      => '#1E90FF',
                    "borderColor"          => '#000000',
                    "borderSize"           => 0,
                    "borderRadius"         => 5,
                    "isHover"              => false,
                    "hoverColor"           => '',
                    "hoverBackgroundColor" => '#1E90FF',
                    "hoverBorderColor"     => '#000000',
                    "hoverBorderSize"      => 0,
                    "hoverIconColor"       => '',
                    'transition'           => 1,
                    "fullWidth"            => true,
                    "url"                  => 'https://example.com',
                    "newTab"               => true,
                    "contentAlignment"     => 'center',
                    "alignment"            => 'center',
                    "fontWeight"           => [],
                    "margin"               => $default_margin_padding,
                    "padding"              => $default_margin_padding,
                    "enableIcon"           => false,
                    "iconColor"            => '',
                    "iconFontSize"         => 10,
                    'iconName'             => 'ninja-table',
                    'iconPosition'         => 'left',
                    'itemSpacing'          => 0
                ],

            ],
            [
                "name"    => __("Icon", "ninja-tables"),
                "type"    => "icon", // (unique)
                "icon"    => "el-icon-finished",
                "has_pro" => true,
                "value"   => "ninja-table",
                "style"   => [
                    "color"     => '#000000',
                    "fontSize"  => 10,
                    "alignment" => 'center',
                    "margin"    => $default_margin_padding,
                    "padding"   => $default_margin_padding
                ],
            ],
            [
                "name"    => __("Rating", "ninja-tables"),
                "type"    => "star_rating", // (unique)
                "icon"    => "el-icon-star-off",
                "has_pro" => false,
                "value"   => 5,
                "style"   => [
                    "color"           => '',
                    "fontSize"        => 10,
                    "maxStar"         => 5,
                    "alignment"       => 'center',
                    "showRatingValue" => true,
                    "margin"          => $default_margin_padding,
                    "padding"         => $default_margin_padding
                ],
            ],
            [
                "name"    => __("List", "ninja-tables"),
                "type"    => "list", // (unique)
                "icon"    => "el-icon-s-operation",
                "has_pro" => false,
                "value"   => ['list item 1', 'list item 2', 'list item 3'],
                "style"   => [
                    "listType"    => 'ul',
                    'color'       => '',
                    "fontSize"    => 10,
                    "alignment"   => 'center',
                    "fontWeight"  => [],
                    "lineHeight"  => 20,
                    'listStyle'   => 'circle',
                    'itemSpacing' => 0,
                    "margin"      => $default_margin_padding,
                    "padding"     => $default_margin_padding
                ]
            ],
            [
                "name"    => __("HTML", "ninja-tables"),
                "type"    => "custom_html", // (unique)
                "icon"    => "el-icon-edit",
                "has_pro" => false,
                "value"   => "<span style='display: block; text-align: center; line-height: 1.1'>custom html</span>",
                "style"   => [
                    "margin"  => $default_margin_padding,
                    "padding" => $default_margin_padding,
                ]
            ],
            [
                "name"    => __("Shortcode", "ninja-tables"),
                "type"    => "shortcode", // (unique)
                "icon"    => "el-icon-document-copy",
                "has_pro" => false,
                "value"   => "[Shortcode]",
                "style"   => [
                    "margin"    => $default_margin_padding,
                    "padding"   => $default_margin_padding,
                    "alignment" => 'center',
                ],
            ],
            [
                "name"    => __("Image", 'ninja-tables'),
                "type"    => "image", // (unique)
                "icon"    => "el-icon-picture-outline",
                "has_pro" => true,
                "value"   => NINJA_TABLES_DIR_URL . "assets/img/ninja-table-editor-button-2x.png",
                "style"   => [
                    'alignment' => 'center',
                    'size'      => 50,
                    'shape'     => 'square',
                    'alt'       => 'Demo Image',
                    'link'      => 'https://www.example.com/',
                    'target'    => 'blank',
                    "margin"    => $default_margin_padding,
                    "padding"   => $default_margin_padding
                ],
            ],
        ],
    ],
    "advance" => [
        "name"   => __("Advanced", "ninja-tables"),
        "key"    => "advance", // unique
        "fields" => [
            [
                "name"    => __("Stylist List", "ninja-tables"),
                "type"    => "stylist_list", // (unique)
                "icon"    => "el-icon-notebook-2",
                "has_pro" => true,
                "value"   => ['list item 1', 'list item 2', 'list item 3'],
                "style"   => [
                    "iconColor"    => '',
                    "iconFontSize" => 10,
                    'iconName'     => 'heart',
                    "listType"     => 'ul',
                    'itemSpacing'  => 0,
                    'color'        => '',
                    "fontSize"     => 10,
                    "alignment"    => 'center',
                    "fontWeight"   => [],
                    "lineHeight"   => 20,
                    "margin"       => $default_margin_padding,
                    "padding"      => $default_margin_padding
                ]
            ],
            [
                "name"    => __("Ribbon", "ninja-tables"),
                "type"    => "ribbon", // (unique)
                "icon"    => "el-icon-collection-tag",
                "has_pro" => true,
                "value"   => "Ribbon",
                "style"   => [
                    'ribbonType'       => 'side',
                    "margin"           => $default_margin_padding,
                    "padding"          => $default_margin_padding,
                    'ribbonPosition'   => 'left',
                    "color"            => '',
                    'fontSize'         => 10,
                    'backgroundColor'  => '#C71585',
                    'width'            => 245,
                    'height'           => 10,
                    'bookmarkHeight'   => 36,
                    'bookmarkWidth'    => 55,
                    'sideHeight'       => 17,
                    'sideWidth'        => 55,
                    'horizontalWidth'  => 170,
                    'horizontalHeight' => 25,
                    'xAxis'            => -10,
                    'yAxis'            => -11,
                    'cornerXAxis'      => -13,
                    'horizontalXAxis'  => -10,
                    'textXAxis'        => 0,
                    'textYAxis'        => 0,
                    "fontWeight"       => [],
                ]
            ],
            [
                "name"    => __("Progress", "ninja-tables"),
                "type"    => "progress", // (unique)
                "icon"    => "el-icon-circle-plus-outline",
                "has_pro" => false,
                "value"   => "",
                "style"   => [
                    "color"      => '',
                    "percentage" => 50,
                    "width"      => 100,
                    "thickness"  => 6,
                    "alignment"  => 'center',
                    'type'       => 'circle',
                    'fontSize'   => 10,
                    "margin"     => $default_margin_padding,
                    "padding"    => $default_margin_padding
                ],
            ],
            [
                "name"    => __("Text Icon", "ninja-tables"),
                "type"    => "text_icon", // (unique)
                "icon"    => "el-icon-notebook-1",
                "has_pro" => true,
                "value"   => "Enter text...",
                "style"   => [
                    "iconColor"    => '',
                    "iconFontSize" => 10,
                    'iconName'     => 'ninja-table',
                    'iconPosition' => 'left',
                    'itemSpacing'  => 0,
                    'color'        => '',
                    "fontSize"     => 10,
                    "alignment"    => 'center',
                    "fontWeight"   => [],
                    "margin"       => $default_margin_padding,
                    "padding"      => $default_margin_padding,
                ]
            ]
        ],
    ],
];
