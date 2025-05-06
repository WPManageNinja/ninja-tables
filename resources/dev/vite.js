const { glob } = require("glob");
const fs = require("fs");
const path = require("path");
const arguments = process.argv;

let mode = 'dev';
let switchTo = 'production';

if (arguments[2] === '--build') {
    mode = 'production';
    switchTo = 'dev';
}

const modeTitle = mode === 'dev' ? 'Development' : 'Production';
const regexObj = new RegExp(`["']env["']\\s+=>\\s*["']` + switchTo + `["'],?`, 'g');

const copyDir = (src, dest) => {
    if (!fs.existsSync(dest)) {
        fs.mkdirSync(dest, { recursive: true });
    }

    const entries = fs.readdirSync(src, { withFileTypes: true });

    for (const entry of entries) {
        const srcPath = path.join(src, entry.name);
        const destPath = path.join(dest, entry.name);

        if (entry.isDirectory()) {
            copyDir(srcPath, destPath);
        } else {
            fs.copyFileSync(srcPath, destPath);
        }
    }
};

const deleteDir = (dirPath) => {
    if (fs.existsSync(dirPath)) {
        fs.readdirSync(dirPath).forEach((file) => {
            const curPath = path.join(dirPath, file);
            if (fs.lstatSync(curPath).isDirectory()) {
                deleteDir(curPath);
            } else {
                fs.unlinkSync(curPath);
            }
        });
        fs.rmdirSync(dirPath);
    }
};

glob(['config/app.php']).then((files) => {
    files.forEach((item) => {
        let data = fs.readFileSync(item, 'utf8');
        let result = data.replace(regexObj, "'env'            => '" + mode + "',");

        fs.writeFile(item, result, 'utf8', function (err) {
            if (err) return console.log(err);
        });

        console.log(`✅ ${modeTitle} asset enqueued!`);
    });
});

// Icons management
const sourceIcons = path.resolve(__dirname, '../libs/icons');
const destIcons = path.resolve(__dirname, '../public/icons');

if (mode === 'dev') {
    try {
        if (!fs.existsSync(destIcons)) {
            fs.symlinkSync(sourceIcons, destIcons, 'junction');
            console.log('🔗 Symlink created: public/icons → libs/icons');
        } else {
            console.log('ℹ️ Symlink or folder already exists at public/icons');
        }
    } catch (err) {
        console.error('❌ Error creating symlink:', err);
    }
} else if (mode === 'production') {
    try {
        if (fs.existsSync(destIcons)) {
            fs.unlinkSync(destIcons);
            console.log('🗑️ Symlink or icons folder removed from public/icons for production.');
        }
    } catch (err) {
        console.error('❌ Error deleting symlink or folder:', err);
    }
}