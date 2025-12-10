<?php

if (!defined('ABSPATH')) {
    die(__FILE__);
}

function ninjaTablesGetFluentProductAtrributes()
{
    $attributes = [
        'product_price'        => 'Product Price',
        'product_price_range'  => 'Product Price Range',
        'product_variations'   => 'Product Variations',
        'product_stock'        => 'Product Stock',
        'product_quantity'     => 'Product Quantity input field',
        'buy_now_button'       => 'Buy Now Button',
        'stock_availability'   => 'Stock Availability',
    ];

    return [
        "key"         => 'product_data',
        'source_type' => 'product_data',
        "label"       => 'Product Data',
        "instruction" => 'Show Product Data Attributes',
        "value_type"  => 'options',
        "placeholder" => 'Select Data Attribute',
        "options"     => $attributes
    ];
}

function ninjaTablesGetPostColumnAttributes()
{
    $attributes = [
        'post_author'   => 'Post Author',
        'post_date'     => 'Post Date',
        'post_title'    => 'Product Title',
        'post_excerpt'  => 'Product Short Description',
        'post_content'  => 'Product Long Description',
        'post_status'   => 'Post Status',
    ];

    return [
        "key"         => 'post_data',
        'source_type' => 'post_data',
        "label"       => 'Post Data',
        "instruction" => 'Show Post Data Attributes',
        "value_type"  => 'options',
        "placeholder" => 'Select Data Attribute',
        "options"     => $attributes
    ];
}

function ninjaTablesGetPostDynamicColumnAtrributes(): array
{
    $types = [];
    $types[] = [
        "key"             => 'post_meta',
        'source_type'     => 'custom',
        "label"           => 'Post Meta',
        "placeholder"     => 'Type Post Meta key',
        "instruction"     => 'You can populate any Post Meta. Please provide the name of the meta key then your table column values will be populated for corresponding row',
        "learn_more_url"  => 'https://wpmanageninja.com/docs/ninja-tables/wp-posts-table/custom-column-on-wp-posts-table/',
        "learn_more_text" => 'Learn more about Post Meta integration',
        "value_type"      => 'text'
    ];
    $types[] = [
        "key"             => 'shortcode',
        'source_type'     => 'shortcode',
        "label"           => 'Shortcode / Computed Value or HTML',
        "placeholder"     => 'Provide any valid HTML / Computed fields, Please check instruction / documentation for advance usage',
        "instruction"     => 'You can add any type of HTML or customized dynamic field / shortcode as the column value. You add dynamic post/post meta/acf field like as below: <br><ul><li>For Post Field: {post.ID} / {post.post_title} / {post.permalink}</li><li>For Post Meta: {postmeta.POSTMETA_KEY_NAME}</li></ul>',
        "learn_more_url"  => '',
        "learn_more_text" => 'Please read the documentation for more details and advanced usage',
        "value_type"      => 'textarea'
    ];

    $imageSizes          = get_intermediate_image_sizes();
    $formattedImageSizes = [];
    foreach ($imageSizes as $imageSize) {
        $formattedImageSizes[$imageSize] = $imageSize;
    }

    $types[] = [
        "key"         => 'featured_image',
        'source_type' => 'custom',
        "label"       => 'Featured Image',
        "instruction" => 'Show Featured image with post link / without link',
        "value_type"  => 'options',
        "placeholder" => 'Select Image Size',
        "options"     => $formattedImageSizes
    ];

    return $types;
}


$ninja_tables_fields   = ninjaTablesGetPostDynamicColumnAtrributes();
$ninja_tables_fields[] = ninjaTablesGetFluentProductAtrributes();
$ninja_tables_fields[] = ninjaTablesGetPostColumnAttributes();

return $ninja_tables_fields;
