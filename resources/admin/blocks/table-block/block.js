import Edit from "./Edit";
import Save from "./Save";

const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;

registerBlockType('ninja-tables/guten-block', {
    title: __('Ninja Tables'),
    icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 321.98 249.25"><path class="A" d="M312.48 249.25H9.5a9.51 9.51 0 0 1-9.5-9.5V9.5A9.51 9.51 0 0 1 9.5 0h303a9.51 9.51 0 0 1 9.5 9.5v230.25a9.51 9.51 0 0 1-9.52 9.5zM9.5 7A2.53 2.53 0 0 0 7 9.5v230.25a2.53 2.53 0 0 0 2.5 2.5h303a2.53 2.53 0 0 0 2.5-2.5V9.5a2.53 2.53 0 0 0-2.5-2.5z"/><path class="A" d="M75 44.37h8.75v202.7H75z"/><path class="B" d="M129.37 44.37"/><path class="C" d="M249.37 44.37"/><path class="A" d="M6.16.5h309.66a6 6 0 0 1 6 6v43.8a.63.63 0 0 1-.63.63H.8a.63.63 0 0 1-.63-.63V6.5a6 6 0 0 1 6-6zM4.88 142.84h312.6v15.1H4.88zM22.47 90h28.27v16.97H22.47zm89.13 0h165.67v16.97H111.6zM22.47 190h28.27v16.97H22.47zm89.13 0h165.67v16.97H111.6z"/></svg>,
    category: 'widgets',
    keywords: [
        __('Ninja Tables'),
        __('Gutenberg Table'),
        __('Table'),
        __('Data Tables'),
        __('ninja-tables-gutenberg-block')
    ],
    attributes: {
        tableId: {
            type: 'string',
            default: ''
        },
        dataSource: {
            type: 'string',
            default: ''
        },
        activeDesign: {
            type: 'string',
            default: 'styling'
        },
        tableSettings: {
            type: 'object',
            default: {}
        }
    },

    supports: {
        customClassName: false
    },

    edit: Edit,

    save: Save
});
