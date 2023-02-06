import './footableExtends';
import ninjaTableApp from './_tableApp';
window.ninjaTableApp = ninjaTableApp;
import diacriticsRemoval from "./diacriticsRemoval";
window.ninjaTableApp.diacriticsRemoval = diacriticsRemoval;

jQuery(document).ready(function () {
    ninjaTableApp.initTables();
});
