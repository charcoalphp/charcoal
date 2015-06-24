/**
* grunt watch task
*/
module.exports = {
	json: {
		files:[
			'*.json',
			'config/*.json'
		],
		tasks: ['jsonlint']
	},
	php: {
		files :[
			'src/**/*.php',
			'tests/**/*.php',
		],
		tasks: ['phplint']
	}
};
