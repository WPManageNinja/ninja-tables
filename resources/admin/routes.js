import Home from './components/Home.vue';
import AllTables from './components/AllTables.vue';
import Tools from './components/Tools/Tools.vue';
import ImportTable from './components/Tools/Import.vue';
import PermissionSettings from './components/Tools/Privacy.vue';
import DefaultTableAppearance from './components/Tools/DefaultAppearance.vue';
import LicenseSettings from './components/Tools/License.vue';
import GlobalSettings from './components/Tools/GlobalSettings.vue';
import Charts from './components/Charts.vue';

import TableHome from './components/Table/TableHome.vue';
import TableDataItems from './components/Table/TableRows.vue';
import TableColumns from './components/Table/ColumnEditor/TableColumns.vue';
import ExportImport from './components/Table/Tools/ExportImport.vue';
import Help from './components/Extras/Help.vue';
import TableAdditionalCss from './components/Table/TableAdditionalCss.vue';
import TableDesignStudio from './components/Table/TableDesignStudio.vue';
import TableEditing from './components/Table/Editing/Editing.vue';

import AddOrEditTable from './components/TableBuilder/TableBuilderHome'

export const routes = [
    {
        name: 'default',
        path: '*',
        redirect: '/'
    },
    {
        path: '/',
        component: Home,
        props: true,
        children: [
            {
                path: '/',
                name: 'home',
                component: AllTables
            },
            {
                path: '/tools',
                component: Tools,
                children: [
                    {
                        path: '',
                        name: 'import_tables',
                        component: ImportTable
                    },
                    {
                        path: 'default_table_appearance',
                        name: 'default_table_appearance',
                        component: DefaultTableAppearance
                    },
                    {
                        path: 'permission',
                        name: 'permission',
                        component: PermissionSettings
                    },
                    {
                        path: 'licensing',
                        name: 'licensing',
                        component: LicenseSettings
                    },
                    {
                        path: 'global_settings',
                        name: 'global_settings',
                        component: GlobalSettings
                    }
                ]
            },
            {
                path: '/help',
                name: 'help',
                component: Help
            }
        ]
    },
    {
        path: '/tables/:table_id',
        component: TableHome,
        props: true,
        children: [
            {
                path: '',
                name: 'data_items',
                component: TableDataItems
            },
            {
                path: 'columns',
                name: 'data_columns',
                component: TableColumns
            },
            {
                path: 'design_studio',
                name: 'design_studio',
                component: TableDesignStudio
            },
            {
                path: 'additional_css',
                name: 'additional_css',
                component: TableAdditionalCss
            },
            {
                path: 'import-export',
                name: 'import-export',
                component: ExportImport
            },
            {
                path: 'table_editing',
                name: 'table_editing',
                component: TableEditing
            },
        ]
    },
    {
        path: '/charts',
        component: Charts,
        props: true
    },
    {
        path: '/table_builder_edit_table/:table_id',
        name: 'table_builder_edit_table',
        component: AddOrEditTable,
        props: true
    },
    {
        path: '/table_builder_add_table',
        name: 'table_builder_add_table',
        component: AddOrEditTable,
        props: true
    },
];
