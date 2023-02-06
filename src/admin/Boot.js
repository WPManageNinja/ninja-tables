import NINJATABLE from './NINJATABLES';

var oldOnError = window.onerror;
window.onerror = function() {
    if(oldOnError) oldOnError.apply(this, arguments);  // Call any previously assigned handler
    return true;
};

window.NINJATABLE = new NINJATABLE();
