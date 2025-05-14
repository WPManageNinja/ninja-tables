import generateColorCss from "./colorUtils";
import {tableLibs} from "../../../data/data";
export const hasPro = !!window.ninja_table_admin?.hasPro;

export const customColorCss = generateColorCss;
export const tableLibrary = tableLibs;
export const availableTables = window.ninja_table_admin?.availableTables || [];


