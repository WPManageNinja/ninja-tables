<?php

use NinjaTables\App\App;

/**
 * Globally-accessible functions
 *
 * @link           https://authlab.io
 * @since          1.0.0
 *
 * @package        wp_table_data_press
 * @subpackage     wp_table_data_press/includes
 *
 * @param        $tableId
 * @param string $scope
 *
 * @return array
 */
if ( ! function_exists('ninja_table_get_table_columns')) {
    function ninja_table_get_table_columns($tableId, $scope = 'public')
    {
        $tableColumns = get_post_meta($tableId, '_ninja_table_columns', true);
        if ( ! $tableColumns || ! is_array($tableColumns)) {
            $tableColumns = array();
        }

        return apply_filters('ninja_get_table_columns_' . $scope, $tableColumns, $tableId);
    }
}

if ( ! function_exists('ninja_table_get_table_settings')) {
    function ninja_table_get_table_settings($tableId, $scope = 'public')
    {
        $tableSettings        = get_post_meta($tableId, '_ninja_table_settings', true);
        $defaultTableSettings = getDefaultNinjaTableSettings();
        if ( ! $tableSettings) {
            $tableSettings = $defaultTableSettings;
        } else {
            if (empty($tableSettings['css_classes'])) {
                $tableSettings['css_classes'] = array();
            }

            if (empty($tableSettings['stacks_devices'])) {
                $tableSettings['stacks_devices'] = array();
            }

            if (empty($tableSettings['stacks_appearances'])) {
                $tableSettings['stacks_appearances'] = array();
            }
        }

        return apply_filters('ninja_get_table_settings_' . $scope, $tableSettings, $tableId);
    }
}


if ( ! function_exists('getDefaultNinjaTableSettings')) {
    function getDefaultNinjaTableSettings()
    {
        $renderType = defined('NINJATABLESPRO') ? 'legacy_table' : 'ajax_table';
        $settings   = get_option('_ninja_table_default_appearance_settings');
        $defaults   = array(
            "perPage"            => 20,
            "show_all"           => false,
            "library"            => 'footable',
            "css_lib"            => 'semantic_ui',
            "enable_ajax"        => false,
            "css_classes"        => array(),
            "enable_search"      => true,
            "column_sorting"     => true,
            "default_sorting"    => 'old_first',
            "sorting_type"       => "by_created_at",
            "table_color"        => 'ninja_no_color_table',
            "render_type"        => $renderType,
            "frontend_loader"    => 'yes',
            "table_color_type"   => 'pre_defined_color',
            "expand_type"        => 'default',
            'stackable'          => 'no',
            'stacks_devices'     => array(),
            'stacks_appearances' => array(),
            'table_font_family'  => 'inherit',
            'table_font_size'    => 14,
        );
        if ( ! $settings) {
            $defaults['css_classes'] = array(
                'selectable',
                'striped',
                'vertical_centered'
            );
        }
        if ( ! $settings) {
            $settings = array();
        }
        $settings = wp_parse_args($settings, $defaults);

        return apply_filters('get_default_ninja_table_settings', $settings);
    }
}

if ( ! function_exists('ninja_table_admin_role')) {
    function ninja_table_admin_role()
    {
        if (current_user_can('administrator')) {
            return 'administrator';
        }
        $roles = apply_filters('ninja_table_admin_role', array('administrator'));
        if (is_string($roles)) {
            $roles = array($roles);
        }
        foreach ($roles as $role) {
            if (current_user_can($role)) {
                return $role;
            }
        }

        return false;
    }
}

if ( ! function_exists('ninja_tables_db_table_name')) {
    function ninja_tables_db_table_name()
    {
        return 'ninja_table_items';
    }
}

if ( ! function_exists('ninja_table_renameDuplicateValues')) {
    function ninja_table_renameDuplicateValues($values)
    {
        $result = array();

        $scale = array_count_values(array_unique($values));

        foreach ($values as $item) {
            if ($scale[$item] == 1) {
                $result[] = $item;
            } else {
                $result[] = $item . '-' . $scale[$item];
            }

            $scale[$item]++;
        }

        return $result;
    }
}

if ( ! function_exists('ninja_table_is_in_production_mood')) {
    function ninja_table_is_in_production_mood()
    {
        return apply_filters('ninja_table_is_in_production_mood', false);
    }
}


function ninjaTablesGetTablesDataByID(
    $tableId,
    $tableColumns = [],
    $defaultSorting = false,
    $disableCache = false,
    $limit = false,
    $skip = false,
    $ownOnly = false
) {
    $providerName = ninja_table_get_data_provider($tableId);
    $providerName = in_array($providerName, array('csv', 'google-csv')) ? 'csv' : $providerName;

    return apply_filters(
        'ninja_tables_fetching_table_rows_' . $providerName,
        array(),
        $tableId,
        $defaultSorting,
        $limit,
        $skip,
        $ownOnly
    );
}

function ninjaTablesClearTableDataCache($tableId)
{
    update_post_meta($tableId, '_ninja_table_cache_object', false);
    update_post_meta($tableId, '_ninja_table_cache_html', false);
    update_post_meta($tableId, '_external_cached_data', false);
    update_post_meta($tableId, '_last_external_cached_time', false);
    update_post_meta($tableId, '__ninja_cached_table_html', false);
}

/**
 * Determine if the table's data has been migrated for manual sorting.
 *
 * @param int $tableId
 *
 * @return bool
 */
function ninjaTablesDataMigratedForManualSort($tableId)
{
    // The post meta table would have a flag that the data of
    // the table is migrated to use for the manual sorting.
    $postMetaKey = '_ninja_tables_data_migrated_for_manual_sort';

    return ! ! get_post_meta($tableId, $postMetaKey, true);
}

/**
 * Determine if the user wants to disable the caching for the table.
 *
 * @param int $tableId
 *
 * @return bool
 */
function ninja_tables_shouldNotCache($tableId)
{
    $tableSettings = ninja_table_get_table_settings($tableId, 'public');

    return (
        isset($tableSettings['shouldNotCache']) && $tableSettings['shouldNotCache'] == 'yes'
    ) ? true : false;
}

/**
 * Get the ninja table icon url.
 *
 * @return string
 */
function ninja_table_get_icon_url()
{
    return 'data:image/svg+xml;base64,'
           . base64_encode('<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
              viewBox="0 0 80 80" xml:space="preserve">
                  <g>
                     <g>
                        <polyline points="0.6,51.2 18.4,51.2 18.4,38.6 0.6,38.6" fill="#ffffff" />
                        <path d="M0.6,63.1c0,1,0,1.9,0.2,2.8h17.6V53.5H0.6" fill="#ffffff"/>
                        <path d="M0.6,22.6" fill="#ffffff"/>
                        <path d="M0.6,21.5h78.8v-4.7c0-9-7.5-16.2-16.7-16.2H20.4h-3C8.1,0.5,0.6,7.8,0.6,16.7" fill="#ffffff"/>
                        <polyline points="0.6,36.3 18.4,36.3 18.4,23.8 0.6,23.8" fill="#ffffff"/>
                        <rect x="20.6" y="38.6" width="58.8" height="12.5" fill="#ffffff"/>
                        <rect x="20.6" y="23.8" width="58.8" height="12.4" fill="#ffffff"/>
                        <path d="M79.3,65.9c0.1-1.1,0.1-1.8,0.1-2.7v-9.7H20.6v12.4L79.3,65.9" fill="#ffffff"/>
                     </g>
                        <path d="M18.4,79.3L18.4,79.3v-11H1.5v0.1c2.2,6.4,8.5,11,15.9,11L18.4,79.3L18.4,79.3z" fill="#ffffff"/>
                        <path d="M78.6,68.3h-58v11v0.1h42.1C70.1,79.4,76.4,74.8,78.6,68.3C78.6,68.4,78.6,68.4,78.6,68.3" fill="#ffffff"/>
                  </g>
                </svg>');
}

if ( ! function_exists('ninja_tables_is_valid_url')) {
    define('NINJA_TABLES_URL_FORMAT',
        '/^(https?):\/\/' .                                         // protocol
        '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+' .         // username
        '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?' .      // password
        '@)?(?#' .                                                  // auth requires @
        ')((([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*' .                      // domain segments AND
        '[a-z][a-z0-9-]*[a-z0-9]' .                                 // top level domain  OR
        '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}' .
        '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])' .                 // IP address
        ')(:\d+)?' .                                                // port
        ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*' . // path
        '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)' .      // query string
        '?)?)?' .                                                   // path and query string optional
        '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?' .      // fragment
        '$/i');
    function ninja_tables_is_valid_url($url)
    {
        return preg_match(NINJA_TABLES_URL_FORMAT, $url);
    }
}

function ninja_tables_allowed_html_tags()
{
    $tags = wp_kses_allowed_html('post');

    // form fields - input
    $tags['input'] = [
        'class' => [],
        'id'    => [],
        'name'  => [],
        'value' => [],
        'type'  => [],
        'src'   => []
    ];
    // select
    $tags['select'] = [
        'class' => [],
        'id'    => [],
        'name'  => [],
        'value' => [],
        'type'  => [],
    ];
    // select options
    $tags['option'] = [
        'selected' => [],
    ];
    // style
    $tags['style'] = [
        'types' => [],
    ];
    // iframe
    $tags['iframe'] = [
        'width'           => [],
        'height'          => [],
        'src'             => [],
        'srcdoc'          => [],
        'title'           => [],
        'frameborder'     => [],
        'allow'           => [],
        'class'           => [],
        'id'              => [],
        'allowfullscreen' => [],
        'style'           => [],
    ];
    // form
    $tags['form'] = [
        'target' => [],
        'action' => [],
        'method' => [],
    ];
    //button
    $tags['button']['onclick'] = [];

    //svg
    if (empty($tags['svg'])) {
        $svg_args = array(
            'svg'   => array(
                'class'           => true,
                'aria-hidden'     => true,
                'aria-labelledby' => true,
                'role'            => true,
                'xmlns'           => true,
                'width'           => true,
                'height'          => true,
                'viewbox'         => true, // <= Must be lower case!
            ),
            'g'     => array('fill' => true),
            'title' => array('title' => true),
            'path'  => array(
                'd'    => true,
                'fill' => true,
            )
        );
        $tags     = array_merge($tags, $svg_args);
    }

    return apply_filters('ninja_tables/allowed_html_tags', $tags);
}

function ninja_tables_allowed_css_properties()
{
    add_filter('safe_style_css', function ($styles) {
        $style_tags = ['display', 'opacity', 'visibility'];
        $style_tags = apply_filters('ninja_tables/allowed_css_properties', $style_tags);

        foreach ($style_tags as $tag) {
            $styles[] = $tag;
        }

        return $styles;
    });
}

if ( ! function_exists('ninja_tables_sanitize_array')) {
    function ninja_tables_sanitize_array(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = ninja_tables_sanitize_array($value);
            } else {
                $array[$key] = wp_kses($value, ninja_tables_allowed_html_tags());
            }
        }

        return $array;
    }
}

function ninja_tables_sanitize_table_content_array(array $array, $tableId)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = ninja_tables_sanitize_array($value);
        } else {
            $array[$key] = sanitize_post_field('post_content', $value, $tableId, 'db');
        }
    }

    return $array;
}


function ninjaTableGetExternalCachedData($tableId)
{
    $tableSettings = get_post_meta($tableId, '_ninja_table_settings', true);
    if ( ! isset($tableSettings['caching_interval']) && $tableSettings['caching_interval']) {
        return false;
    }
    $intervalMinutes = intval($tableSettings['caching_interval']);
    if ( ! $intervalMinutes) {
        return false;
    }
    $interval       = $intervalMinutes * 60;
    $lastCachedTime = intval(get_post_meta($tableId, '_last_external_cached_time', true));

    if ((time() - $lastCachedTime) < $interval) {
        return get_post_meta($tableId, '_external_cached_data', true);
    }

    return false;
}

function ninjaTableSetExternalCacheData($tableId, $data)
{
    $tableSettings = get_post_meta($tableId, '_ninja_table_settings', true);
    if ( ! isset($tableSettings['caching_interval']) && $tableSettings['caching_interval']) {
        return false;
    }

    update_post_meta($tableId, '_last_external_cached_time', time());
    update_post_meta($tableId, '_external_cached_data', $data);
}

if ( ! function_exists('getNinjaFluentFormMenuIcon')) {
    function getNinjaFluentFormMenuIcon()
    {
        $icon = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><defs><style>.cls-1{fill:#fff;}</style></defs><title>dashboard_icon</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M15.57,0H4.43A4.43,4.43,0,0,0,0,4.43V15.57A4.43,4.43,0,0,0,4.43,20H15.57A4.43,4.43,0,0,0,20,15.57V4.43A4.43,4.43,0,0,0,15.57,0ZM12.82,14a2.36,2.36,0,0,1-1.66.68H6.5A2.31,2.31,0,0,1,7.18,13a2.36,2.36,0,0,1,1.66-.68l4.66,0A2.34,2.34,0,0,1,12.82,14Zm3.3-3.46a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,10.53Zm0-3.73a2.36,2.36,0,0,1-1.66.68H3.21a2.25,2.25,0,0,1,.68-1.64,2.36,2.36,0,0,1,1.66-.68H16.79A2.25,2.25,0,0,1,16.12,6.81Z"/></g></g></svg>');

        return apply_filters('fluent_form_menu_icon', $icon);
    }
}


if ( ! function_exists('ninjaTablesGetPostStatuses')) {
    function ninjaTablesGetPostStatuses()
    {
        $post_status = [
            ['key' => 'publish', 'label' => 'Publish'],
            ['key' => 'pending', 'label' => 'Pending'],
            ['key' => 'draft', 'label' => 'Draft'],
            ['key' => 'auto-draft', 'label' => 'Auto Draft'],
            ['key' => 'future', 'label' => 'Future'],
            ['key' => 'private', 'label' => 'Private'],
            ['key' => 'inherit', 'label' => 'Inherit'],
            ['key' => 'trash', 'label' => 'Trash'],
            ['key' => 'any', 'label' => 'Any']
        ];

        return apply_filters('ninja_table_post_status', $post_status);
    }
}

if ( ! function_exists('ninja_table_get_data_provider')) {
    function ninja_table_get_data_provider($tableId)
    {
        $provider = get_post_meta($tableId, '_ninja_tables_data_provider', true);
        if ( ! $provider) {
            $provider = 'default';
        }

        return $provider;
    }
}

if ( ! function_exists('ninja_table_format_header')) {
    function ninja_table_format_header($header)
    {
        $acceptedChars = array(
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        );

        $data           = array();
        $column_counter = 1;
        foreach ($header as $item) {
            $string = trim(strip_tags($item));
            $string = strtolower($string);
            $chars  = str_split($string);
            $key    = '';
            foreach ($chars as $char) {
                if (in_array($char, $acceptedChars)) {
                    $key .= $char;
                }
            }
            $key     = sanitize_title($key, 'ninja_column_' . $column_counter, 'display');
            $counter = 1;
            while (isset($data[$key])) {
                $key .= '_' . $counter;
                $counter++;
            }
            $data[$key] = $item;
            $column_counter++;
        }

        return $data;
    }
}

if ( ! function_exists('ninja_table_url_slug')) {
    function ninja_table_url_slug($str, $options = array())
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter'     => '_',
            'limit'         => null,
            'lowercase'     => true,
            'replacements'  => array(),
            'transliterate' => true,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'D',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ő' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ű' => 'U',
            'Ý' => 'Y',
            'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'd',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ő' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ű' => 'u',
            'ý' => 'y',
            'þ' => 'th',
            'ÿ' => 'y',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A',
            'Β' => 'B',
            'Γ' => 'G',
            'Δ' => 'D',
            'Ε' => 'E',
            'Ζ' => 'Z',
            'Η' => 'H',
            'Θ' => '8',
            'Ι' => 'I',
            'Κ' => 'K',
            'Λ' => 'L',
            'Μ' => 'M',
            'Ν' => 'N',
            'Ξ' => '3',
            'Ο' => 'O',
            'Π' => 'P',
            'Ρ' => 'R',
            'Σ' => 'S',
            'Τ' => 'T',
            'Υ' => 'Y',
            'Φ' => 'F',
            'Χ' => 'X',
            'Ψ' => 'PS',
            'Ω' => 'W',
            'Ά' => 'A',
            'Έ' => 'E',
            'Ί' => 'I',
            'Ό' => 'O',
            'Ύ' => 'Y',
            'Ή' => 'H',
            'Ώ' => 'W',
            'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a',
            'β' => 'b',
            'γ' => 'g',
            'δ' => 'd',
            'ε' => 'e',
            'ζ' => 'z',
            'η' => 'h',
            'θ' => '8',
            'ι' => 'i',
            'κ' => 'k',
            'λ' => 'l',
            'μ' => 'm',
            'ν' => 'n',
            'ξ' => '3',
            'ο' => 'o',
            'π' => 'p',
            'ρ' => 'r',
            'σ' => 's',
            'τ' => 't',
            'υ' => 'y',
            'φ' => 'f',
            'χ' => 'x',
            'ψ' => 'ps',
            'ω' => 'w',
            'ά' => 'a',
            'έ' => 'e',
            'ί' => 'i',
            'ό' => 'o',
            'ύ' => 'y',
            'ή' => 'h',
            'ώ' => 'w',
            'ς' => 's',
            'ϊ' => 'i',
            'ΰ' => 'y',
            'ϋ' => 'y',
            'ΐ' => 'i',
            // Turkish
            'Ş' => 'S',
            'İ' => 'I',
            'Ç' => 'C',
            'Ü' => 'U',
            'Ö' => 'O',
            'Ğ' => 'G',
            'ş' => 's',
            'ı' => 'i',
            'ç' => 'c',
            'ü' => 'u',
            'ö' => 'o',
            'ğ' => 'g',
            // Russian
            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'Yo',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'J',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sh',
            'Ъ' => '',
            'Ы' => 'Y',
            'Ь' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'yo',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sh',
            'ъ' => '',
            'ы' => 'y',
            'ь' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            // Ukrainian
            'Є' => 'Ye',
            'І' => 'I',
            'Ї' => 'Yi',
            'Ґ' => 'G',
            'є' => 'ye',
            'і' => 'i',
            'ї' => 'yi',
            'ґ' => 'g',
            // Czech
            'Č' => 'C',
            'Ď' => 'D',
            'Ě' => 'E',
            'Ň' => 'N',
            'Ř' => 'R',
            'Š' => 'S',
            'Ť' => 'T',
            'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c',
            'ď' => 'd',
            'ě' => 'e',
            'ň' => 'n',
            'ř' => 'r',
            'š' => 's',
            'ť' => 't',
            'ů' => 'u',
            'ž' => 'z',
            // Polish
            'Ą' => 'A',
            'Ć' => 'C',
            'Ę' => 'e',
            'Ł' => 'L',
            'Ń' => 'N',
            'Ó' => 'o',
            'Ś' => 'S',
            'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A',
            'Č' => 'C',
            'Ē' => 'E',
            'Ģ' => 'G',
            'Ī' => 'i',
            'Ķ' => 'k',
            'Ļ' => 'L',
            'Ņ' => 'N',
            'Š' => 'S',
            'Ū' => 'u',
            'Ž' => 'Z',
            'ā' => 'a',
            'č' => 'c',
            'ē' => 'e',
            'ģ' => 'g',
            'ī' => 'i',
            'ķ' => 'k',
            'ļ' => 'l',
            'ņ' => 'n',
            'š' => 's',
            'ū' => 'u',
            'ž' => 'z',
        );

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
}


function ninjaTableInsertDataToTable($tableId, $values, $header)
{
    $header      = array_keys($header);
    $time        = current_time('mysql');
    $headerCount = count($header);
    $timeStamp   = time();
    $userId      = get_current_user_id();
    $datas       = [];

    foreach ($values as $index => $item) {
        if ($headerCount == count($item)) {
            $itemTemp = array_combine($header, $item);
        } else {
            // The item can have less/more entry than the header has.
            // We have to ensure that the header and values match.
            $itemTemp = array_combine(
                $header,
                // We'll get the appropriate values by merging Array1 & Array2
                array_merge(
                // Array1 = Only the entries that the header has.
                    array_intersect_key($item, array_fill_keys(array_values($header), null)),
                    // Array2 = The remaining header entries will be blank.
                    array_fill_keys(array_diff(array_values($header), array_keys($item)), null)
                )
            );
        }

        $data = array(
            'table_id'   => $tableId,
            'attribute'  => 'value',
            'owner_id'   => $userId,
            'value'      => json_encode($itemTemp, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s', $timeStamp + $index),
            'updated_at' => $time
        );

        if (isset($item['position']) && defined('NINJAPROPLUGIN_VERSION')) {
            $data['position'] = $item['position'];
        }

        $datas[] = $data;
    }

    // We are gonna batch insert by small chunk so that we can avoid PHP
    // memory issue or MYSQL max_allowed_packet issue for large data set.
    global $wpdb;
    $tableName = $wpdb->prefix . ninja_tables_db_table_name();
    foreach (array_chunk($datas, 3000) as $chunk) {
        ninjtaTableBatchInsert($tableName, $chunk);
    }
}

function ninjaTablePerChunk($table_id = false)
{
    return apply_filters('ninja_table_per_chunk', 3000, $table_id);
}

function ninja_table_clear_all_cache($posts = array())
{
    $tables = [];

    if ( ! empty($posts)) {
        $tables = $posts;
    } else {
        $tables = \NinjaTables\App\Models\Post::select('ID')
                                              ->where('post_type', 'ninja-table')
                                              ->get();
    }

    foreach ($tables as $table) {
        ninjaTablesClearTableDataCache($table->ID);
    }

    return true;
}

/**
 * Batch insert data using raw SQL query.
 *
 * @param string $table
 * @param array $rows
 *
 * @return bool|int
 */
function ninjtaTableBatchInsert($table, $rows)
{
    global $wpdb;

    // Extract column list from first row of data
    $columns = array_keys($rows[0]);
    asort($columns);
    $columnList = '`' . implode('`, `', $columns) . '`';
    // Start building SQL, initialise data and placeholder arrays
    $sql          = "INSERT INTO `$table` ($columnList) VALUES\n";
    $placeholders = array();
    $data         = array();
    // Build placeholders for each row, and add values to data array
    foreach ($rows as $row) {
        ksort($row);
        $rowPlaceholders = array();
        foreach ($row as $key => $value) {
            $data[]            = $value;
            $rowPlaceholders[] = is_numeric($value) ? '%d' : '%s';
        }
        $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
    }
    // Stitch all rows together
    $sql .= implode(",\n", $placeholders);

    // Run the query.  Returns number of affected rows.
    return $wpdb->query($wpdb->prepare($sql, $data));
}

/**
 * Normalize every item, i.e. make string "true" to boolean true
 *
 * @param array $data
 *
 * @return array
 */
function ninjaTableNormalize($data = [])
{
    foreach ($data as $key => $item) {
        if ($item == 'false') {
            $item = false;
        }

        if ($item == 'true') {
            $item = true;
        }

        if (is_array($item)) {
            $item = array_map('sanitize_text_field', $item);
        } else {
            $item = sanitize_text_field($item);
        }

        $data[$key] = $item;
    }

    return $data;
}

/**
 * Parse the given html content get the table IDs from the matched shortcodes.
 *
 * @param string $content
 *
 * @return array
 */
function ninjaTablesGetShortCodeIds($content)
{
    $tags = ['ninja_tables', 'ninja_table_builder'];

    $ids = [];

    foreach ($tags as $tag) {
        if (false === strpos($content, '[')) {
            return [];
        }

        preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return [];
        }

        foreach ($matches as $shortcode) {
            if ($tag === $shortcode[2]) {
                // Replace braces with empty string.
                $parsedCode = str_replace(['[', ']', '&#91;', '&#93;', '\\'], '', $shortcode[0]);

                $result = shortcode_parse_atts($parsedCode);

                if ( ! empty($result['id'])) {
                    $ids[$result['id']] = $result['id'];
                }
            }
        }
    }

    return $ids;
}

/**
 * Validate nonce.
 */
function ninjaTablesValidateNonce($key = 'ninja_table_admin_nonce')
{
    $nonce = \NinjaTables\Framework\Support\Arr::get($_REQUEST, $key);

    if ( ! wp_verify_nonce($nonce, $key)) {
        $errors = apply_filters('ninja_tables_nonce_error', [
            '_ninjatablesnonce' => [
                __('Nonce verification failed, please try again.', 'ninja-tables')
            ]
        ]);

        wp_send_json(['errors' => $errors], 422);
    }
}

if ( ! function_exists('ninjaTablesPrintSafeVar')) {
    function ninjaTablesPrintSafeVar($content, $esc_func = false)
    {
        if ($esc_func) {
            echo call_user_func($esc_func, $content);
        }
        // PHPCS - This content var is hardcoded variable or already escaped the contents by esc_* functions.
        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

if ( ! function_exists('ninjaTablesEscCss')) {
    function ninjaTablesEscCss($css)
    {
        if (preg_match('#</?\w+#', $css)) {
            return '';
        }

        return $css;
    }
}

/**
 * Checks if a string starts with something
 *
 * @param string $haystack
 * @param array $needles
 *
 * @return bool
 */
function ninjaTablesStartsWith($haystack, $needles)
{
    if (is_array($haystack)) {
        $haystack = implode(' ', $haystack);
    }

    foreach ((array)$needles as $needle) {
        if ('' != $needle && substr($haystack, 0, strlen($needle)) === (string)$needle) {
            return true;
        }
    }

    return false;
}

/**
 * Sanitizes CSV value
 *
 * @param string $content
 *
 * @return string $content
 */
function ninjaTablesSanitizeForCSV($content)
{
    $formulas = ['=', '-', '+', '@', "\t", "\r"];

    if (ninjaTablesStartsWith($content, $formulas)) {
        $content = "'" . $content;
    }

    return $content;
}

/**
 * @param string $data
 *
 * @return mixed $data
 */
function ninjaTablesEscapeScript($data)
{
    return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $data);
}

function ninjaTablesCanUnfilteredHTML()
{
    return current_user_can('unfiltered_html') || apply_filters('ninja_tables_disable_fields_sanitize', false);
}

function ninjaTablesIsNotice($key = 'admin_notice')
{
    $prefix = 'ninja_tables_';

    if (isset($_COOKIE[$prefix . $key])) {
        $plugin_version = sanitize_text_field($_COOKIE[$prefix . $key]);

        if ($plugin_version == NINJA_TABLES_VERSION) {
            return false;
        }
    }

    return true;
}

// function only for supported ninja charts
function ninja_tables_boot()
{
    return true;
}


function ninjaTablesExternalClearPageCaches()
{
    // clear wp lightspeed caches
    if (defined('LSCWP_V')) {
        do_action('litespeed_purge', 'ninja_tables_light_speed_clear_cache');
    }

    // clear wp redis caches
    if (defined('NGINX_HELPER_BASEURL')) {
        do_action('rt_nginx_helper_purge_all');
    }

    // clear wp rocket caches
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }

    // clear godaddy internal caches
    if (class_exists('\WPaaS\Cache')) {
        if (has_action('shutdown', ['\WPaaS\Cache', 'ban'])) {
            return;
        }

        remove_action('shutdown', ['\WPaaS\Cache', 'purge'], PHP_INT_MAX);
        add_action('shutdown', ['\WPaaS\Cache', 'ban'], PHP_INT_MAX);
    }

    // clear wp-fastest caches
    if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
        $GLOBALS['wp_fastest_cache']->deleteCache();
    }

    // clear wp cache caches
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    // clear autooptimizepress caches
    if (class_exists('autoptimizeCache')) {
        \autoptimizeCache::clearall();
    }

    // clear wp-optimize caches
    if (class_exists('WPO_Page_Cache')) {
        (new \WPO_Page_Cache())->purge();
    }

    // clear SiteGround Optimizer caches
    if (function_exists('sg_cachepress_purge_cache')) {
        sg_cachepress_purge_cache();
    }

    // clear cloudflare caches
    if (defined('CLOUDFLARE_PLUGIN_DIR') && class_exists('CF\WordPress\Hooks')) {
        (new \CF\WordPress\Hooks())->purgeCacheEverything();
    }
}

