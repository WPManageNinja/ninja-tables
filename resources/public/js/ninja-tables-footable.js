import './footableExtends';
import ninjaTableApp from './_tableApp';
window.ninjaTableApp = ninjaTableApp;
import diacriticsRemoval from "./diacriticsRemoval";
window.ninjaTableApp.diacriticsRemoval = diacriticsRemoval;

jQuery(document).ready(function () {
    const delay = (typeof ninja_footables !== "undefined" && ninja_footables.delay) || 0;
    setTimeout(() => {
        ninjaTableApp.initTables();
    }, delay);
});
