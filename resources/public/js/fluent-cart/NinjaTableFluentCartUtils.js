const $ = window.jQuery;

class NinjaTableFluentCartUtils {
    static init($table, settings) {
        this.$table = $table;
        this.settings = settings;
        this.bindEvents();
    }

    static bindEvents() {
        this.bindVariationChange();
        this.bindQuantity();
        this.bindAddToCart();
    }

    static getNewUrl(url, itemId, quantity) {
        const newUrl = new URL(url);
        newUrl.searchParams.set('item_id', itemId);
        newUrl.searchParams.set('quantity', quantity);
        return newUrl.toString();
    }

    static bindVariationChange() {
        this.$table.on('change', '.nt_fct_variations_select', function (e) {
            const $sel = $(this);
            const $opt = $sel.find(':selected');
            const $row = $sel.closest('tr');
            const pid = $sel.closest('.nt_fct_variations_wrapper').data('product_id');
            const vid = $opt.data('variation_id') || 0;
            const price = $opt.data('formatted_price') || '';
            const comp = $opt.data('compared_price') || '';
            const img = $opt.data('image_src') || '';
            const stock = $opt.data('stock') || 0;
            const productType = $sel.closest('.nt_fct_variations_wrapper').data('product_type') || ''
            const isCompareRow = $row.data('is_compare_row') || false;
            const isRowId = $row.data('row_id') || false;

           // $rowParentFind used for stackable table
            const $rowParentFind = (sel) => {
                const $el = $row.find(sel);
                if ($el.length) return $el;

                return $row.parent().find(sel);
            };

            let $priceWrap = $rowParentFind('.nt_fct_product_price_wrapper');

            if (isCompareRow) {
                $priceWrap = $rowParentFind('[data-pid="'+pid+'"] .nt_fct_product_price_wrapper');
            }

            $priceWrap.find('.nt_fct_product_price').text(price);
            const $del = $priceWrap.find('.nt-fct-compared-price');
            comp ? $del.text(comp).show() : $del.hide();

            let $imgEl = $rowParentFind('.fct_product_image');

            if (isRowId) {
                $imgEl = $rowParentFind(`[data-row_id="${$row.data('row_id')}"] .fct_product_image`);
            } else if (isCompareRow) {
                $imgEl = $rowParentFind(`[data-pid="${pid}"] .fct_product_image`);
            }

            if (img && $imgEl) $imgEl.attr('src', img);

            let $stockEl = $rowParentFind('.nt_fct_product_stock');

            if (isCompareRow) {
                $stockEl = $rowParentFind('[data-pid="'+pid+'"] .nt_fct_product_stock');
            }

            if ($stockEl.length) {
                $stockEl.attr('data-product_stock', stock).text(stock);
            }

            let $btn = $rowParentFind(`.nt_fct_add_to_cart_${pid}`);
            $btn.attr('data-variation_id', vid);

            if (productType === 'digital') {
                $btn = $rowParentFind(`.nt_fct_buy_now_${pid}`);
                $btn.attr('data-variation_id', vid);

                const a = $row.find('.nt_fct_add_cart_wrapper a')
                const qty = a.data('quantity') || 1;
                const newUrl = NinjaTableFluentCartUtils.getNewUrl(a.attr('href'), vid, qty)
                a.attr('href', newUrl)
            }
        });
    }

    static bindQuantity() {
        this.$table
            .on('change', '.nt_fct_quantity', function () {
                const $input = $(this);
                const $productType = $input.data('product_type');
                const pid = $input.data('product_id');
                const qty = Math.max(1, parseInt($input.val(), 10) || 1);
                $input.val(qty);
                let $btn = NinjaTableFluentCartUtils.$table.find(`.nt_fct_add_to_cart_${pid}`);

                if ($productType === 'digital') {
                    $btn = NinjaTableFluentCartUtils.$table.find(`.nt_fct_buy_now_${pid}`);
                }

                $btn.attr('data-quantity', qty);
                const vid = $btn.data('variation_id');

                if ($productType === 'digital') {
                    const newUrl = NinjaTableFluentCartUtils.getNewUrl($btn.attr('href'), vid, qty)
                    $btn.attr('href', newUrl)
                }
            });
    }

    static bindAddToCart() {
        this.$table.on('click', '[class*="nt_fct_add_to_cart_"]:not(.loading)', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const $parent = $btn.parent();

            if ($btn.data('processing')) return;
            $btn.data('processing', true);
            $parent.addClass('loading');
            $btn.prop('disabled', true);

            const dataset = e.target?.dataset;
            if (!dataset) {
                return;
            }

            const product = {
                product_id: dataset.product_id,
                variation_id: parseInt(dataset.variation_id),
                quantity: dataset.quantity
            };

            NinjaTableFluentCartUtils._ajaxAdd([product], $btn, $parent);
        });
    }

    static _ajaxAdd(products, $button, $parent, settings) {
        const {ajax_url, ninja_table_public_nonce} = window.ninja_footables || {};
        $.post(ajax_url, {
            action: 'ninja_table_wp_fct_add_to_cart',
            nonce: ninja_table_public_nonce,
            table_id: settings?.table_id || this.settings.table_id,
            products: products
        })
            .done(r => {
                if (r?.success && r.cart_html) {
                    if ($('.ninjatable_cart_wrapper.fluent-cart').length) {
                        $('.ninjatable_cart_wrapper.fluent-cart').replaceWith(r.cart_html);
                    } else {
                        $('.ninja_table_wrapper').append(r.cart_html);
                    }

                    $parent && $parent.addClass('added').removeClass('loading');
                    setTimeout(() => {
                        $parent && $parent.removeClass('loading');
                        $button && $button.prop('disabled', false).removeData('processing');
                    }, 1000);
                } else {
                    $parent && $parent.removeClass('loading');
                    $button && $button.prop('disabled', false).removeData('processing');
                }
            })
            .fail(() => {
                $parent && $parent.removeClass('loading');
                $button && $button.prop('disabled', false).removeData('processing');
            });
    }
}

export default NinjaTableFluentCartUtils;
