export default (function ($) {
    let instance = null;

    return (function() {
    	if (!instance) {
            instance = jQuery({});
        }
        return instance;
    })();
})(window.jQuery);