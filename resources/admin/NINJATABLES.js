import ElementPlus from './elements';
import { createRouter } from 'vue-router';
import Rest from "./Bits/Rest";
import { applyFilters, addFilter, addAction, doAction } from '@wordpress/hooks';

export default class NINJATABLE {
    constructor() {
        // WordPress hooks
        this.applyFilters = applyFilters;
        this.addFilter = addFilter;
        this.addAction = addAction;
        this.doAction = doAction;

        // Vue related properties
        this.ElementPlus = ElementPlus;
        this.createRouter = createRouter;
    }

    registerBlock(blockLocation, blockName, block) {
        addFilter(blockLocation, 'ninja_table', function (components) {
            components[blockName] = block;
            return components;
        });
    }

    registerTopMenu(title, route) {
        // Maybe we need some validation here for the provided route
        addFilter('ninja_table_top_menus', 'ninja_table', function (menus) {
            menus.push({
                route: route.name,
                title: title
            });
            return menus;
        });

        addFilter('ninja_table_global_routes', 'ninja_table', function (routes) {
            routes.push(route);
            return routes;
        });
    }

    registerTableTabs(title, route) {
        // Maybe we need some validation here for the provided route
        addFilter('ninja_table_table_tabs', 'ninja_table', function (menus) {
            menus.push({
                route: route.name,
                title: title
            });
            return menus;
        });

        addFilter('ninja_table_global_routes', 'ninja_table', function (routes) {
            routes.push(route);
            return routes;
        });
    }

    $getJSON(options) {
        options['nonce'] = window.ninja_table_admin.ninja_table_admin_nonce;
        return window.jQuery.getJSON(window.ajaxurl, options);
    }

    $get(url, options = {}) {
        // if condition only for pro
        if (url.action) {
            options = url;
            options['ninja_table_admin_nonce'] = window.ninja_table_admin.ninja_table_admin_nonce;
            return window.jQuery.get(window.ajaxurl, options);
        } else {
            return Rest.get(url, options);
        }
    }

    $post(url, options = {}) {
        // if condition only for pro
        if (url.action) {
            options = url;
            options['ninja_table_admin_nonce'] = window.ninja_table_admin.ninja_table_admin_nonce;
            return window.jQuery.post(window.ajaxurl, options);
        } else {
            return Rest.post(url, options);
        }
    }

    $del(url, options = {}) {
        return Rest.delete(url, options);
    }

    $put(url, options = {}) {
        return Rest.put(url, options);
    }

    $patch(url, options = {}) {
        return Rest.patch(url, options);
    }
}
