/**
* @file File Watcher
*
* Executes lint tasks on PHP and JSON files.
*/

module.exports = {
    php: {
        files: [
            'src/**/*.php',
            'tests/**/*.php'
        ],
        tasks: ['phplint']
    }
};
