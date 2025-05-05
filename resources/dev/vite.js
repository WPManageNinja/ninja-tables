const {glob} = require("glob")
const fs = require('fs');


const arguments = process.argv;

let mode = 'dev';
let switchTo = 'production';


if (typeof arguments[2] !== 'undefined' && arguments[2] === '--build') {
    mode = 'production';
    switchTo = 'dev';
}

console.log(mode, switchTo)

const modeTitle = mode === 'dev' ? 'Development' : 'Production';
const regexObj = new RegExp(`["']env["']\\s+=>\\s*["']` + switchTo + `["'],?`, 'g');


const newFiles = glob(['config/app.php'])
newFiles.then(function (files) {
    files.forEach(function (item, index, array) {
        let data = fs.readFileSync(item, 'utf8');
        let result = data.replace(regexObj, "'env'            => '" + mode + "',");

        fs.writeFile(item, result, 'utf8', function (err) {
            if (err) return console.log(err);
        });
        console.log(`✅ ${modeTitle} asset enqueued!`);
    });
})