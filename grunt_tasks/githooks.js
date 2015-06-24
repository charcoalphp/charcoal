/**
* githooks grunt task
*/
module.exports = {
	all: {
		'pre-commit': 'jsonlint phplint phpunit phpcs',
	}
};
