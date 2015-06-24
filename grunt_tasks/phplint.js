/**
* phplint grunt task
*/
module.exports = {
	
	src: [
		'src/**/*.php'
	],
	tests: [
		'tests/**/*.php'
	],
	options: {
		swapPath: '/tmp',
		phpArgs : {
			// add -f for fatal errors
			'-lf': null
		}
	}
};
