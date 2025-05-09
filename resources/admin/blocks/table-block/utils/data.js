import generateColorCss from "./colorUtils";
import {tableLibs} from "../../../data/data";
export {default as instanceUID} from "../hooks/useCustomHook";
export const hasPro = !!window.ninja_table_admin?.hasPro;

export const customColorCss = generateColorCss;
export const tableLibrary = tableLibs;


