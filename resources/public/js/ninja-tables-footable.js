import './footableExtends';
import ninjaTableApp from './_tableApp';
window.ninjaTableApp = ninjaTableApp;
import diacriticsRemoval from "./diacriticsRemoval";
window.ninjaTableApp.diacriticsRemoval = diacriticsRemoval;

jQuery(document).ready(function () {
    setTimeout(() => {
        ninjaTableApp.initTables();
    }, window?.ninja_footables?.delay || 0);
});
