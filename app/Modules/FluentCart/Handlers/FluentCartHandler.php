<?php

namespace NinjaTables\App\Modules\FluentCart\Handlers;

use FluentCart\Api\Resource\FrontendResource\CartResource;
use NinjaTables\App\App;
use NinjaTables\App\Modules\FluentCart\Traits\FluentCartTrait;
use NinjaTables\Framework\Support\Arr;
use FluentCart\App\Helpers\CartHelper;
use FluentCart\Api\StoreSettings;
use FluentCart\Api\CurrencySettings;

class FluentCartHandler
{
    use FluentCartTrait;

    private $__queryable_postColumns__ = [];

    public function getFluentCartOptions()
    {
        $settings = [
            'product-categories' => [
                'title'       => 'Select Products By Category',
                'description' => 'Select the categories from where you want to show the products. Leave empty if you want to show from all categories',
                'terms'       => get_terms([
                    'taxonomy'   => 'product-categories',
                    'hide_empty' => false,
                ])
            ],
            'product-types'      => [
                'title'       => 'Select Products By Product Brands',
                'description' => 'Select the product brands from where you want to show the products. Leave empty if you want to show from all types',
                'terms'       => get_terms([
                    'taxonomy'   => 'product-brands',
                    'hide_empty' => false,
                ])
            ]
        ];

        wp_send_json_success([
            'query_terms' => $settings
        ], 200);
    }

    public function createFluentCartTable()
    {
        $messages   = array();
        $inputs     = App::getInstance('request')->all();
        $post_title = sanitize_text_field(Arr::get($inputs, 'post_title'));
        if ($post_title === '') {
            $messages['title'] = __('The title field is required.', 'ninja-tables');
        }

        // If Validation failed
        if (array_filter($messages)) {
            wp_send_json_error(array('message' => $messages), 422);
            wp_die();
        }

        $initialHeaders = App::getInstance('config')->get('fluent-cart-headers');

        $tableId = $this->saveTable();

        update_post_meta($tableId, '_ninja_wp_posts_query_extra', $this->getQueryExtra($tableId));

        $message = 'Table created successfully.';

        $query_selections = ninja_tables_sanitize_array(Arr::get($inputs, 'query_selections', []));
        $query_conditions = ninja_tables_sanitize_array(Arr::get($inputs, 'query_conditions', []));

        update_post_meta($tableId, '_ninja_table_fct_query_selections', $query_selections);
        update_post_meta($tableId, '_ninja_table_fct_query_conditions', $query_conditions);

        $appearanceSettings = [
            'show_cart_before_table' => 'yes',
            'show_cart_after_table'  => 'yes',
            'show_cart_button'       => 'yes',
            'show_checkout_button'   => 'yes'
        ];

        update_post_meta($tableId, '_ninja_table_fct_appearance_settings', $appearanceSettings);
        update_post_meta($tableId, '_ninja_table_columns', $initialHeaders);
        update_post_meta($tableId, '_ninja_tables_data_provider', 'wp_fct');

        wp_send_json_success(array('table_id' => $tableId, 'message' => $message), 200);
    }

    public function getTableData()
    {
        if (!current_user_can(ninja_table_admin_role())) {
            return;
        }

        $inputs = App::getInstance('request')->all();
        $tableId     = intval(Arr::get($inputs, 'table_id'));
        $perPage     = intval(Arr::get($inputs, 'per_page', 20));
        $currentPage = intval(Arr::get($inputs, 'page', 1));
        $skip        = ($currentPage - 1) * $perPage;

        $columns = get_post_meta($tableId, '_ninja_table_columns', true);

        $products      = $this->getProducts($tableId);
        $formattedData = $this->formatProductData($columns, $products);
        $total         = count($formattedData);

        $paginatedData = array_slice($formattedData, $skip, $perPage);

        $data = [
            'data'        => $paginatedData,
            'data_source' => 'wp_fct',
            'total'       => $total
        ];

        return wp_send_json($data, 200);
    }

    public function formatProductData($columns, $rows)
    {
        $formattedData = [];


        foreach ($rows as $index => $row) {
            $formattedRow = [
                'id'       => $index + 1,
                'position' => $index + 1,
                'values'   => []
            ];

            foreach ($columns as $column) {
                $key                          = $column['key'];
                $data                         = $this->setColumnData($column, $row);
                $formattedRow['values'][$key] = $data;
            }

            $formattedData[] = $formattedRow;
        }

        return $formattedData;
    }

    protected function saveTable($postId = null)
    {
        $inputs = App::getInstance('request')->all();

        $attributes = array(
            'post_title'  => sanitize_text_field(Arr::get($inputs, 'post_title')),
            'post_type'   => 'ninja-table',
            'post_status' => 'publish'
        );

        if (!$postId) {
            $postId = wp_insert_post($attributes);
        } else {
            $attributes['ID'] = $postId;
            wp_update_post($attributes);
        }

        return $postId;
    }

    public function getTableSettings($table)
    {
        $table->dataSourceType = get_post_meta($table->ID, '_ninja_tables_data_provider', true);

        $table->isEditable        = false;
        $table->isExportable      = true;
        $table->isImportable      = false;
        $table->isSortable        = false;
        $table->isCreatedSortable = false;
        $table->hasCacheFeature   = false;

        $metaKeys = [
            'query_selections'    => '_ninja_table_fct_query_selections',
            'query_conditions'    => '_ninja_table_fct_query_conditions',
            'appearance_settings' => '_ninja_table_fct_appearance_settings',
        ];

        foreach ($metaKeys as $property => $metaKey) {
            $value            = get_post_meta($table->ID, $metaKey, true);
            $table->$property = $value ?: (object)[];
        }

        return $table;
    }

    protected function getQueryExtra($tableId)
    {
        $queryExtra = get_post_meta($tableId, '_ninja_wp_posts_query_extra', true);

        if (!$queryExtra || $queryExtra == 'false') {
            $queryExtra = [
                'query_limit'     => 6000,
                'order_by_column' => 'ID',
                'order_by'        => 'DESC'
            ];
        }

        if (empty($queryExtra['query_limit'])) {
            $queryExtra['query_limit'] = 7000;
        }

        return apply_filters('ninja_table_wp_posts_query_extra', $queryExtra, $tableId);
    }

    public function saveQuerySettings(): void
    {
        if (!current_user_can(ninja_table_admin_role())) {
            return;
        }
        ninjaTablesValidateNonce();

        $inputs  = App::getInstance('request')->all();
        $tableId = intval(Arr::get($inputs, 'table_id'));

        if (!$tableId) {
            wp_send_json_error(array('message' => 'Table not found'), 400);
        }

        $data = [
            'query_selections'    => ninja_tables_sanitize_array(Arr::get($inputs, 'query_selections', [])),
            'query_conditions'    => ninja_tables_sanitize_array(Arr::get($inputs, 'query_conditions', [])),
            'appearance_settings' => ninja_tables_sanitize_array(Arr::get($inputs, 'appearance_settings', []))
        ];

        update_post_meta($tableId, '_ninja_table_fct_query_selections', $data['query_selections']);
        update_post_meta($tableId, '_ninja_table_fct_query_conditions', $data['query_conditions']);
        update_post_meta($tableId, '_ninja_table_fct_appearance_settings', $data['appearance_settings']);

        wp_send_json_success(array('message' => 'Settings successfully updated'), 200);
    }

    public function getCustomFieldOptions(): void
    {
        if (!current_user_can(ninja_table_admin_role())) {
            return;
        }

        ninjaTablesValidateNonce();

        $fields = App::getInstance('config')->get('fluent-cart-dynamic-column-option');

        if (post_type_exists('fluent-products')) {
            $fields[] = $this->getProductTaxonomyFields('fluent-products');
        }

        wp_send_json_success([
            'custom_fields' => $fields
        ]);
    }

    public function getProductTaxonomyFields($postType): array
    {
        $taxonomies = get_object_taxonomies($postType);

        $attributes = [];
        foreach ($taxonomies as $taxonomy) {
            $attributes[$taxonomy] = ucwords(str_replace('-', ' ', $taxonomy));
        }

        return [
            "key"         => 'tax_data',
            'source_type' => 'tax_data',
            "label"       => 'Product Taxonomy',
            "instruction" => 'Show Product Categories & Type From Here',
            "value_type"  => 'options',
            "placeholder" => 'Select Data Attribute',
            "options"     => $attributes
        ];
    }


    public function fetchData($data, $tableId, $defaultSorting, $limitEntries = false, $skip = false)
    {
        $perPage = -1;

        if ($limitEntries) {
            $perPage = $limitEntries;
        }

        return $this->getPosts($tableId, $perPage, $skip);
    }

    public function getPosts($tableId, $per_page = -1, $offset = 0)
    {
        $frontendColumns = $this->getFrontendColumns($tableId);
        $products        = $this->getProducts($tableId);

        return $this->formatFrontendProductData($frontendColumns, $products);
    }

    public function formatFrontendProductData($columns, $products)
    {
        $formattedData = [];

        foreach ($products as $index => $product) {
            foreach ($columns as $column) {
                $key                = $column['key'];
                $data               = $this->setColumnData($column, $product);
                $formattedRow[$key] = $data;
            }

            $formattedData[] = $formattedRow;
        }

        return $formattedData;
    }

    public function addFrontendAsset($tableArray)
    {
        $assets  = App::getInstance('url.assets');
        $tableId = intval(Arr::get($tableArray, 'table_id'));

        wp_enqueue_script(
            'ninja_table_fluent_cart_frontend_js',
            $assets . "/js/fluent-cart/fct_table_frontend.js",
            ['jquery'],
            NINJA_TABLES_VERSION,
            'all'
        );

        $storeSettings = new StoreSettings();
        $cartUrl       = $storeSettings->getCartPage();
        $checkOutUrl   = $storeSettings->getCheckoutPage();

        wp_localize_script(
            'ninja_table_fluent_cart_frontend_js',
            'NinjaTablesFCTProducts',
            array(
                'cartUrl'     => $cartUrl,
                'checkOutUrl' => $checkOutUrl,
            )
        );

        $appearanceSettings  = get_post_meta($tableId, '_ninja_table_fct_appearance_settings', true);
        $showCartBeforeTable = Arr::get($appearanceSettings, 'show_cart_before_table', 'yes');
        $showCartAfterTable  = Arr::get($appearanceSettings, 'show_cart_after_table', 'yes');

        if ($showCartBeforeTable === 'yes') {
            add_action('ninja_tables_before_table_print', array($this, 'maybeAddCartDom'), 10, 2);
        }
        if ($showCartAfterTable === 'yes') {
            add_action('ninja_tables_after_table_print', array($this, 'maybeAddCartDom'), 10, 2);
        }
    }

    public function maybeAddCartDom($table, $tableArray)
    {
        if (Arr::get($tableArray, 'provider') !== 'wp_fct') {
            return '';
        }
        $tableId = intval(Arr::get($tableArray, 'table_id'));
        echo $this->getCartFragmentHtml($tableId); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function getCartFragmentHtml($tableId)
    {
        if (!defined('FLUENTCART_PLUGIN_PATH')) {
            return;
        }

        $appearanceSettings = get_post_meta($tableId, '_ninja_table_fct_appearance_settings', true);
        $showCheckoutBtn    = Arr::get($appearanceSettings, 'show_checkout_button') === 'yes';
        $showCartBtn        = Arr::get($appearanceSettings, 'show_cart_button') === 'yes';

        $cartInfo = CartHelper::getCart();

        $cartItems = Arr::get($cartInfo, 'cart_data', []);

        $itemCount   = count($cartItems);
        $totalAmount = $this->getCartTotalAmount($cartItems);

        $style = '';
        $class = 'nt-cart-visible';
        if (!$itemCount) {
            $style = 'display: none;';
            $class = '';
        }

        if ($itemCount > 1) {
            $itemText = __('Items', 'ninja-tables');
        } else {
            $itemText = __('Item', 'ninja-tables');
        }

        $storeSettings = new StoreSettings();
        $checkOutUrl   = $storeSettings->getCheckoutPage();
        $cartUrl       = $storeSettings->getCartPage();
        $cartUrl       = apply_filters('ninja_table_fct_cart_url', $cartUrl);

        $defaultCheckoutText = __('Checkout', 'ninja-tables');
        $checkOutText = Arr::get($appearanceSettings, 'checkoutBtnText', $defaultCheckoutText);
        $checkOutText = apply_filters('ninja_table_fct_checkout_text', $checkOutText);

        $defaultCartText = __('View cart', 'ninja-tables');
        $cartText = Arr::get($appearanceSettings, 'cartBtnText', $defaultCartText);
        $cartText = apply_filters('ninja_table_fct_cart_text', $cartText);

        ob_start();
        ?>
        <div style="<?php echo esc_attr($style); ?>" class="<?php echo esc_attr($class);?> ninjatable_cart_wrapper fluent-cart woocommerce widget_shopping_cart">
            <div class="cart_details fct-cart-details">
                <div class="nt_woo_items">
                    <span class="nt_fct_item_count"><?php echo esc_html($itemCount) . ' ' . esc_html($itemText); ?> </span>
                    <span class="nt_fct_separator">|</span>
                    <span class="nt_fct_amount"><?php echo esc_html($totalAmount); ?></span>
                </div>

                <div class="nt_fct_cart_checkout_buttons">
                    <?php
                    if ($showCartBtn): ?>
                        <a class="button fct-forward" href="<?php echo esc_url($cartUrl); ?>">
                            <span class="nt_fct_view_cart">
                                <i class="fooicon fooicon-bag"></i>
                                <?php echo esc_html($cartText); ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if ($showCheckoutBtn): ?>
                        <a class="button checkout fct-forward" href="<?php echo esc_url($checkOutUrl); ?>">
                            <span class="nt_fct_view_cart">
                                <i class="fooicon fooicon-basket"></i>
                                <?php echo esc_html($checkOutText); ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public function addToCart()
    {
        $inputs = App::getInstance('request')->all();
        $nonce = sanitize_text_field(Arr::get($inputs, 'nonce', ''));
        if (!wp_verify_nonce($nonce, 'ninja_table_public_nonce')) {
            wp_send_json([
                'message' => __('Invalid nonce', 'ninja-tables')
            ], 403);
        }

        $products = ninja_tables_sanitize_array(Arr::get($inputs, 'products', []));
        $tableId  = intval(Arr::get($inputs, 'table_id'));

        foreach ($products as $product) {
            $variationId = intval(Arr::get($product, 'variation_id'));
            $quantity    = intval(Arr::get($product, 'quantity'));

            CartResource::update([
                'quantity' => $quantity,
                'item_id'  => $variationId
            ], '');
        }

        wp_send_json([
            'success'   => true,
            'cart_html' => $this->getCartFragmentHtml($tableId)
        ]);
    }

    protected function getCartTotalAmount($cartItems)
    {
        $totalAmount = 0;
        foreach ($cartItems as $item) {
            $totalAmount += Arr::get($item, 'line_total', 0);
        }

        return CurrencySettings::getFormattedPrice($totalAmount);
    }
}
