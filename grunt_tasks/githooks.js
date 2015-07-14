/**
* @file Bind Grunt tasks to Git hooks
*/

module.exports = {
    all: {
        'pre-commit': 'jsonlint phplint phpunit phpcs'
    }
};
