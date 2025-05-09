import Edit from "./Edit";
import Save from "./Save";

const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;

registerBlockType('ninja-tables/table-block', {
    title: __('Ninja Tables'),
    icon: 'grid-view',
    category: 'widgets',
    keywords: [__('table'), __('ninja'), __('data')],
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
            default: 'features'
        },
        tableSettings: {
            type: 'object',
            default: {}
        }
    },

    edit: Edit,

    save: Save
});
