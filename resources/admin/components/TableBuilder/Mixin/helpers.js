export const helpers = {
    methods: {
        id(range = 9) {
            this.length = range;
            this.timestamp = +new Date;
            var _getRandomInt = function (min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            var ts = this.timestamp.toString();
            var parts = ts.split("").reverse();
            var id = "";

            for (var i = 0; i < this.length; ++i) {
                var index = _getRandomInt(0, parts.length - 1);
                id += parts[index];
            }
            return id;
        },
        deepClone(obj) {
            return JSON.parse(JSON.stringify(obj))
        },
        getBoolean(data) {
            return (data === true || data === 'true');
        },
        isEmpty(obj) {
            return obj && Object.keys(obj).length === 0;
        },
        allEqual(arr) {
            return arr.every(v => v === arr[0]);
        },
        greaterThanOneNumber(arr, num) {
            return arr.some(el => el > num);
        },
        keyExist(object, key) {
            for (var prop in object) {
                if (key == prop) {
                    return true;
                }
            }
            return false;
        },
        ucWords(string) {
            var i, frags = string.split('_');
            for (i = 0; i < frags.length; i++) {
                frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
            }
            return frags.join(' ');
        },
        upgradeMessage(){
            const html = 'You are using free version of ninja-tables, <a href="https://wpmanageninja.com/downloads/ninja-tables-pro-add-on/?utm_source=ninja-tables&amp;utm_medium=wp&amp;utm_campaign=wp_plugin&amp;utm_term=upgrade" target="_blank">' +
                'Upgrade To Pro' +
                '</a>';
                
            return this.$message({
                showClose: true,
                message: html,
                dangerouslyUseHTMLString: true,
                type: "warning",
            });
        }
    }
}
