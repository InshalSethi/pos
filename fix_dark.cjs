const fs = require('fs');
const path = require('path');

function walkDir(dir) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        if (isDirectory) walkDir(dirPath);
        else if (f.endsWith('.vue')) {
            let content = fs.readFileSync(dirPath, 'utf8');
            let newContent = content.replace(/dark:[a-zA-Z0-9\-\/]+/g, '');
            // Fix double spaces inside class attributes
            newContent = newContent.replace(/ +/g, ' ');
            newContent = newContent.replace(/ \"/g, '"');
            newContent = newContent.replace(/ \'/g, "'");
            fs.writeFileSync(dirPath, newContent);
        }
    });
}
walkDir('e:/laragon/www/pos/resources/js/admin');
console.log('Fixed dark classes in admin vue files');
