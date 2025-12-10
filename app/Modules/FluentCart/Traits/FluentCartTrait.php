<?php

namespace NinjaTables\App\Modules\FluentCart\Traits;

use FluentCart\App\Models\ProductMeta;
use NinjaTables\Framework\Support\Arr;
use FluentCart\App\Models\Product;
use FluentCart\App\Models\ProductVariation;
use FluentCart\Api\CurrencySettings;

trait FluentCartTrait
{

    public function getFrontendColumns($tableId)
    {
        $columns = get_post_meta($tableId, '_ninja_table_columns', true);

        $formatted_columns = array();
        foreach ($columns as $column) {
            $type               = Arr::get($column, 'source_type');
            $columnKey          = Arr::get($column, 'key');
            $dataType           = Arr::get($column, 'wp_post_custom_data_source_type');
            $dataValue          = Arr::get($column, 'wp_post_custom_data_key');
            $imagePermalinkType = Arr::get($column, 'image_permalink_type'); // linked, lightbox, ''

            $formatted_columns[$columnKey] = array(
                'type'                            => $type,
                'key'                             => $columnKey,
                'permalinked'                     => Arr::get($column, 'permalinked'),
                'permalink_target'                => Arr::get($column, 'permalink_target'),
                'filter_permalinked'              => Arr::get($column, 'filter_permalinked'),
                'taxonomy_separator'              => Arr::get($column, 'taxonomy_separator'),
                'column_type'                     => Arr::get($column, 'column_type'),
                'wp_post_custom_data_value'       => Arr::get($column, 'wp_post_custom_data_value'),
                'wp_post_custom_data_source_type' => $dataType,
                'wp_post_custom_data_key'         => $dataValue,
                'image_permalink_type'            => $imagePermalinkType,
                'column_settings'                 => $column
            );
        }

        return $formatted_columns;
    }

    public function getProducts($tableId)
    {
        $querySelection  = get_post_meta($tableId, '_ninja_table_fct_query_selections', true);
        $queryConditions = get_post_meta($tableId, '_ninja_table_fct_query_conditions', true);

        $orderBy         = Arr::get($queryConditions, 'order_by');
        $orderByType     = Arr::get($queryConditions, 'order_by_type');
        $categories      = Arr::get($querySelection, 'product-categories');
        //TODO: rename product-types to product-brands
        $brands          = Arr::get($querySelection, 'product-types');
        $hideOutOfStock  = Arr::get($queryConditions, 'hide_out_of_stock');

        $query = Product::where('post_type', 'fluent-products')
                        ->where('post_status', 'publish')
                        ->with('variants');

        if ($hideOutOfStock === 'yes') {
            $query->join('fct_product_details', 'fct_product_details.post_id', '=', 'posts.ID')
                  ->where('fct_product_details.stock_availability', 'in-stock');
        }

        if (!empty($categories) || !empty($brands)) {
            if (!empty($categories)) {
                $query->join('term_relationships', 'term_relationships.object_id', '=', 'posts.ID')
                      ->leftJoin('term_taxonomy as category_taxonomy', function ($join) {
                          $join->on('category_taxonomy.term_taxonomy_id', '=', 'term_relationships.term_taxonomy_id')
                               ->where('category_taxonomy.taxonomy', 'product-categories');
                      })
                      ->leftJoin('terms as category_terms', 'category_terms.term_id', '=', 'category_taxonomy.term_id')
                      ->whereIn('category_terms.slug', $categories);
            }

            if (!empty($brands)) {
                $query->join('term_relationships as tr_brands', 'tr_brands.object_id', '=', 'posts.ID')
                      ->leftJoin('term_taxonomy as brand_taxonomy', function ($join) {
                          $join->on('brand_taxonomy.term_taxonomy_id', '=', 'tr_brands.term_taxonomy_id')
                               ->where('brand_taxonomy.taxonomy', 'product-brands');
                      })
                      ->leftJoin('terms as brand_terms', 'brand_terms.term_id', '=', 'brand_taxonomy.term_id')
                      ->whereIn('brand_terms.slug', $brands);
            }
        }

        if ($orderBy === 'post_title' || $orderBy === 'post_date') {
            $query->orderBy($orderBy, $orderByType);
        } elseif ($orderBy === 'price') {
            $query->select('posts.*')
                  ->join('fct_product_variations as fpv', 'posts.ID', '=', 'fpv.post_id')
                  ->orderByRaw('MIN(fpv.item_price) ' . $orderByType);
        } elseif ($orderBy === 'stock') {
            $query->select('posts.*')
                  ->join('fct_product_variations as fpv', 'posts.ID', '=', 'fpv.post_id')
                  ->orderByRaw('CASE WHEN SUM(fpv.available) > 0 THEN 0 ELSE 1 END ASC')
                  ->orderByRaw('SUM(fpv.available) ' . $orderByType);
        }

        $products = $query->groupBy('posts.ID')->get();

        if (!$products->count()) {
            return [];
        }

        return $products;
    }

    protected function setColumnData($column, $row)
    {
        $value     = '';
        $variants  = Arr::get($row, 'variants', []);
        $columnKey = Arr::get($column, 'key', '');

        if ($columnKey === 'fct_product_image') {
            $value = $this->getFeaturedImage($row, $column);
        }


        if ($columnKey === 'fct_post_title') {
            $value = $this->getPostTitle($row, $column);
        }

        if ($columnKey === 'fct_product_price') {
            $value = $this->getProductPrice($row);
        }

        if ($columnKey === 'fct_product_stock') {
            $firstVariantStock = intval(Arr::get($variants, '0.available', 0));
            $value = '<span class="nt_fct_product_stock" data-product_stock="' . esc_attr($firstVariantStock) . '">' . esc_attr($firstVariantStock) . '</span>';
        }

        if ($columnKey === 'fct_product_quantity') {
            ob_start();
            $this->getQunatityInput($row);
            $value = ob_get_clean();
        }

        if ($columnKey === 'fct_product_variations') {
            $value = $this->getVariationsSelect($variants, $row);
        }

        if ($columnKey === 'fct_product_buy') {
            $value = $this->getBuyNowButton($column, $row);
        }

        if ($columnKey === 'fct_product_category') {
            $value = $this->getProductCategories($row, $column, 'product-categories');
        }

        if (Arr::get($column, 'column_type') === 'dynamic_column') {
            $value = $this->handleDynamicColumn($row, $column);
        }

        return $value;
    }

    protected function handleDynamicColumn($row, $column)
    {
        $columnSourceType = Arr::get($column, 'wp_post_custom_data_source_type');

        if ($columnSourceType === 'post_meta') {
            return $this->handleDynamicColumnPostMeta($row, $column);
        } elseif ($columnSourceType === 'tax_data') {
            return $this->handleDynamicColumnTaxonomyData($row, $column);
        } elseif ($columnSourceType === 'post_data') {
            return $this->handleDynamicColumnPostData($row, $column);
        } elseif ($columnSourceType === 'product_data') {
            return $this->handleDynamicColumnProductData($row, $column);
        } elseif ($columnSourceType === 'shortcode') {
            return $this->handleDynamicColumnShortcode($row, $column);
        } elseif ($columnSourceType === 'featured_image') {
            return $this->handleDynamicColumnFeaturedImage($row, $column);
        }

        return '';
    }

    protected function handleDynamicColumnPostMeta($row, $column)
    {
        $metaKey = Arr::get($column, 'wp_post_custom_data_value');

        return get_post_meta(Arr::get($row, 'ID'), $metaKey, true);
    }

    protected function handleDynamicColumnTaxonomyData($row, $column)
    {
        $fieldName = Arr::get($column, 'wp_post_custom_data_key', 'product-types');
        if ($fieldName === 'product-types') {
            $value = Arr::get($row, 'detail.fulfillment_type');

            return ucwords($value);
        } elseif ($fieldName === 'product-categories') {
            return $this->getProductCategories($row, $column, $fieldName);
        }

        return '';
    }

    protected function handleDynamicColumnPostData($row, $column)
    {
        $fieldName = Arr::get($column, 'wp_post_custom_data_key');
        if ($fieldName === 'post_title') {
            return $this->getPostTitle($row, $column);
        } elseif ($fieldName === 'post_status') {
            return ucwords(Arr::get($row, 'post_status'));
        } elseif ($fieldName === 'post_author') {
            $userInfo = get_user_by('ID', Arr::get($row, 'post_author'));
            if ($userInfo) {
                return $userInfo->data->display_name;
            }
        } elseif ($fieldName === 'post_date') {
            $postDate = Arr::get($row, 'post_date');
            if (Arr::get($column, 'column_settings')) {
                $dateFormat = Arr::get($column, 'column_settings.dateFormat');
                $dataType   = Arr::get($column, 'column_settings.data_type');
            } else {
                $dateFormat = Arr::get($column, 'dateFormat');
                $dataType   = Arr::get($column, 'data_type');
            }
            if ($dataType === 'date' && !empty($dateFormat)) {
                return gmdate($this->convertMomentFormatToPhp($dateFormat), strtotime($postDate));
            }

            return $postDate;
        } elseif ($fieldName === 'post_excerpt') {
            return Arr::get($row, 'post_excerpt');
        } elseif ($fieldName === 'post_content') {
            return Arr::get($row, 'post_content');
        }

        return '';
    }

    protected function handleDynamicColumnProductData($row, $column)
    {
        $fieldName = Arr::get($column, 'wp_post_custom_data_key');
        if ($fieldName === 'product_price_range') {
            $minPrice = CurrencySettings::getFormattedPrice(Arr::get($row, 'detail.min_price'));
            $maxPrice = CurrencySettings::getFormattedPrice(Arr::get($row, 'detail.max_price'));

            return $minPrice === $maxPrice ? $minPrice : $minPrice . ' - ' . $maxPrice;
        } elseif ($fieldName === 'product_price') {
            return $this->getProductPrice($row);
        } elseif ($fieldName === 'product_variations') {
            $variants = Arr::get($row, 'variants', []);

            return $this->getVariationsSelect($variants, $row);
        } elseif ($fieldName === 'product_stock') {
            $variants = Arr::get($row, 'variants', []);
            $firstVariantStock = intval(Arr::get($variants, '0.available', 0));

            return '<span class="nt_fct_product_stock" data-product_stock="' . esc_attr($firstVariantStock) . '">' . esc_attr($firstVariantStock) . '</span>';
        } elseif ($fieldName === 'buy_now_button') {
            return $this->getBuyNowButton($column, $row);
        } elseif ($fieldName === 'stock_availability') {
            return str_replace('-', ' ', ucwords(Arr::get($row, 'detail.stock_availability')));
        } elseif ($fieldName === 'product_quantity') {
            ob_start();
            $this->getQunatityInput($row);

            return ob_get_clean();
        }

        return '';
    }

    protected function handleDynamicColumnShortcode($row, $column)
    {
        $shortcode = wp_strip_all_tags(Arr::get($column, 'wp_post_custom_data_value'));
        if (preg_match('/^\{([a-zA-Z0-9_]+)\.([a-zA-Z0-9_]+)\}$/', $shortcode, $matches)) {
            $firstPart  = $matches[1];
            $secondPart = $matches[2];

            if ($firstPart === 'post') {
                return $secondPart === 'permalink' ? Arr::get($row, 'guid') : Arr::get($row, $secondPart);
            } elseif ($firstPart === 'postmeta') {
                return get_post_meta(Arr::get($row, 'ID'), $secondPart, true);
            }
        } else {
            if (Arr::get($column, 'column_settings')) {
                return do_shortcode($shortcode);
            }

            return $shortcode;
        }

        return '';
    }

    protected function handleDynamicColumnFeaturedImage($row, $column)
    {
        return $this->getFeaturedImage($row, $column);
    }

    protected function convertMomentFormatToPhp($format)
    {
        $replacements = [
            'DD'   => 'd',
            'ddd'  => 'D',
            'D'    => 'j',
            'dddd' => 'l',
            'E'    => 'N',
            'o'    => 'S',
            'e'    => 'w',
            'DDD'  => 'z',
            'W'    => 'W',
            'MMMM' => 'F',
            'MM'   => 'm',
            'MMM'  => 'M',
            'M'    => 'n',
            'YYYY' => 'Y',
            'YY'   => 'y',
            'a'    => 'a',
            'A'    => 'A',
            'h'    => 'g',
            'H'    => 'G',
            'hh'   => 'h',
            'HH'   => 'H',
            'mm'   => 'i',
            'ss'   => 's',
            'SSS'  => 'u',
            'zz'   => 'e',
            'X'    => 'U',
        ];

        $phpFormat = strtr($format, $replacements);

        return $phpFormat;
    }

    protected function getQunatityInput($product, $display_type = 'input')
    {
        if (Arr::get($product, 'detail.stock_availability') === 'out-of-stock' && apply_filters(
                'ninjatable_hide_out_stock_cart_btn',
                true,
                $product
            )) {
            return 'Out Of Stock';
        }

        $args = [
            'input_id'     => uniqid('fct_quantity_'),
            'input_name'   => 'fct_product_quantity',
            'input_value'  => '1',
            'step'         => apply_filters('ninja_tables/fct_quantity_input_step', 1, $product),
            'pattern'      => apply_filters('ninja_tables/fct_quantity_input_pattern', '[0-9]*'),
            'inputmode'    => apply_filters('ninja_tables/fct_quantity_input_inputmode', 'numeric'),
            'product_name' => Arr::get($product, 'post_title'),
        ];

        $args = apply_filters('ninja_tables/fct_quantity_input_args', $args, $product);

        extract($args);
        ?>
        <div class="quantity nt-quantity-wrapper nt-noselect nt-display-type-<?php echo esc_attr($display_type); ?>">
            <span class="nt-minus nt-qty-controller nt-noselect"></span>
            <input
                    type="number"
                    data-product_type="<?php echo esc_attr(Arr::get($product, 'detail.fulfillment_type'));?>"
                    data-product_id="<?php
                    echo esc_attr(Arr::get($product, 'ID')); ?>"
                    id="nt_product_qty_<?php
                    echo esc_attr(Arr::get($product, 'ID')); ?>"
                    class="input-text qty text nt_fct_quantity"
                    min="1"
                    step="<?php
                    echo esc_attr($step); ?>"
                    name="<?php
                    echo esc_attr($input_name); ?>"
                    value="<?php
                    echo esc_attr($input_value); ?>"
                    title="<?php
                    echo esc_attr_x('Quantity', 'Product quantity input tooltip', 'ninja-tables'); ?>"
                    size="4"
                    pattern="<?php
                    echo esc_attr($pattern); ?>"
                    inputmode="<?php
                    echo esc_attr($inputmode); ?>"
                    aria-labelledby="<?php
                    echo !empty($args['product_name']) ? sprintf(
                            // translators: %s: Product name
                        esc_attr__('%s quantity', 'ninja-tables'),
                        esc_attr($args['product_name'])
                    ) : ''; ?>"
                    autocomplete="off"/>
            <span class="nt-plus nt-qty-controller nt-noselect"></span>
        </div>
        <?php
    }

    protected function getBuyNowButton($column, $product)
    {
        $productType = Arr::get($product, 'detail.fulfillment_type');
        $productId  = intval(Arr::get($product, 'ID'));
        $buttonText = Arr::get($column, 'column_settings.buy_now_button_text') ?: Arr::get(
            $column,
            'buy_now_button_text'
        ) ?: 'Add To Cart';

        if (Arr::get($product, 'detail.stock_availability') === 'out-of-stock' && apply_filters(
                'ninja-tables/fct/hide_out_stock_cart_btn',
                true,
                $product
            )) {
            return 'Out Of Stock';
        }

        $paymentType      = Arr::get($product, 'variants.0.payment_type');
        $licenseSettings  = ProductMeta::where('object_id', $productId)
                                       ->where('meta_key', 'license_settings')
                                       ->get()->toArray();
        $isLicenceEnabled = Arr::get($licenseSettings, '0.meta_value.enabled', 'no');

        $url = site_url() . '?fluent-cart=instant_checkout&item_id=' . esc_attr(
                Arr::get($product, 'variants.0.id')
            ) . '&quantity=1';

        if ($paymentType === 'subscription' || $isLicenceEnabled === 'yes') {
            $button = sprintf(
                '<a href="%s" target="_blank" data-prouct_type="%s" data-product_id="%d" data-variation_id="%s" data-quantity="1" class="nt_fct_buy_now_%d nt_button nt_button_fct single_buy_now_button button alt fct_product">%s</a>',
                esc_url($url),
                esc_attr($productType),
                esc_attr($productId),
                esc_attr(Arr::get($product, 'variants.0.id')),
                esc_attr($productId),
                esc_html__('Buy Now', 'ninja-tables')
            );
        } else {
            $button = sprintf(
                '<a data-product_id="%d" data-prouct_type="%s" data-variation_id="%s" data-quantity="1" class="nt_fct_add_to_cart_%d nt_button nt_button_fct single_add_to_cart_button button alt fct_product">%s</a>',
                esc_attr($productId),
                esc_attr($productType),
                esc_attr(Arr::get($product, 'variants.0.id')),
                esc_attr($productId),
                esc_html($buttonText)
            );
        }

        return '<div class="nt_fct_add_cart_wrapper">' . $button . '</div>';
    }

    protected function getVariationsSelect($variants, $row)
    {
        $productType = Arr::get($row, 'detail.fulfillment_type');
        $productId   = intval(Arr::get($row, 'ID'));
        $value       = '<div class="nt_fct_variations_wrapper" data-product_type="'.$productType.'" data-product_id="' . $productId . '">';

        if (!empty($variants)) {
            if (gettype($variants) === 'object') {
                $variants = $variants->toArray();
            }

            $firstVariation = reset($variants);

            $value .= '<select class="nt_fct_variations_select" 
              data-nt_variations_select="' . $productId . '"
              data-image_src="' . Arr::get($firstVariation, 'media.meta_value.0.url', '') . '"
              data-current-variation-id="' . Arr::get($firstVariation, 'id') . '"
              data-current-price="' . Arr::get($firstVariation, 'item_price') . '"
              data-current-compared-price="' . Arr::get($firstVariation, 'compare_price') . '">';

            foreach ($variants as $variant) {
                $value .= '<option value="' . Arr::get($variant, 'item_price') . '"
                  data-variation_id="' . Arr::get($variant, 'id') . '"
                  data-image_src="' . Arr::get($variant, 'media.meta_value.0.url', '') . '"
                  data-formatted_price="' . Arr::get($variant, 'formatted_total') . '"
                  data-compared_price="' . CurrencySettings::getFormattedPrice(Arr::get($variant, 'compare_price')) . '"
                  data-stock="' . intval(Arr::get($variant, 'available', 0)) . '"
                  class="nt_fct_variation_option">' . Arr::get($variant, 'variation_title') . '</option>';
            }
            $value .= '</select>';
        }

        $value .= '</div>';

        return $value;
    }

    public function getProductPrice($row)
    {
        $productPrice   = Arr::get($row, 'variants.0.formatted_total');
        $comparedPrice  = Arr::get($row, 'variants.0.compare_price');
        $formattedPrice = CurrencySettings::getFormattedPrice($comparedPrice);

        $value = '<div class="nt_fct_product_price_wrapper">';

        if ($comparedPrice) {
            $value .= '<span><del class="nt-fct-compared-price" data-compared_price="' . esc_attr(
                    $formattedPrice
                ) . '">' . esc_attr($formattedPrice) . '</del> </span>';
        }

        $value .= '<span class="nt_fct_product_price" data-product_price="' . esc_attr($productPrice) . '">' . esc_attr(
                $productPrice
            ) . '</span></div>';

        return $value;
    }

    public function getPostTitle($row, $column)
    {
        $title     = Arr::get($row, 'post_title');
        $permalink = Arr::get($row, 'guid');
        $linked    = Arr::get($column, 'permalinked', 'no');
        $target    = Arr::get($column, 'permalink_target') === '_blank' ? 'target="_blank"' : '';

        if ($linked === 'yes') {
            return '<a href="' . esc_url($permalink) . '" ' . $target . '>' . esc_html($title) . '</a>';
        } else {
            return esc_html($title);
        }
    }

    public function getProductCategories($row, $column, $fieldName)
    {
        $separator = Arr::get($column, 'taxonomy_separator', ', ');

        $atts = '';
        if (Arr::get($column, 'permalinked') == 'yes') {
            if (Arr::get($column, 'filter_permalinked') == 'yes') {
                $atts = ' data-target_column=' . $column['key'] . ' class="ninja_table_permalink ninja_table_do_column_filter" ';
            } elseif (Arr::get($column, 'permalink_target') == '_blank') {
                $atts = ' class="ninja_table_tax_permalink" target="_blank" ';
            } else {
                $atts = ' class="ninja_table_tax_permalink" ';
            }
        }

        $terms = array_map(function ($term) use ($atts) {
            if ($atts) {
                $link = get_term_link($term);

                return "<a " . $atts . " href='{$link}'>{$term->name}</a>";
            }

            return $term->name;
        }, wp_get_post_terms(Arr::get($row, 'ID'), $fieldName));

        if ($terms) {
            return implode($separator, $terms);
        }

        return '';
    }

    public function getFeaturedImage($row, $column)
    {
        $featuredImageUrl = Arr::get($row, 'detail.featured_media.url', '');
        if (empty($featuredImageUrl)) {
            return '';
        }
        $permalink     = Arr::get($row, 'guid', '');
        $linkType      = Arr::get($column, 'image_permalink_type', ''); // linked, lightbox, ''
        $imageSizeType = Arr::get(
            $column,
            'wp_post_custom_data_key',
            'thumbnail'
        ); // thumbnail, medium, medium_large, large
        $sizes         = [
            'thumbnail'    => '100',
            'medium'       => '300',
            'medium_large' => '768',
            'large'        => '1024'
        ];
        $imageSize     = !empty($sizes[$imageSizeType]) ? $sizes[$imageSizeType] : '150';

        $imageTag = sprintf(
            '<img width="%s" class="%s" alt="%s" src="%s" />',   // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
            $imageSize,
            'fct_product_image ' . esc_attr($column['key']),
            esc_attr(Arr::get($row, 'post_title')),
            esc_url($featuredImageUrl)
        );

        if ($linkType === 'linked') {
            $target = Arr::get($column, 'permalink_target') === '_blank' ? ' target="_blank"' : '';

            return sprintf('<a href="%s" %s>%s</a>', esc_url($permalink), $target, $imageTag);
        } elseif ($linkType === 'lightbox') {
            return sprintf('<a class="nt_lightbox" href="%s">%s</a>', esc_url($featuredImageUrl), $imageTag);
        }

        return $imageTag;
    }
}
