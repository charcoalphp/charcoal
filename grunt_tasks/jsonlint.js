/**
* @file Analyse JSON files for potential errors
*/

module.exports = {
    meta: {
        src: ['*.json']
    },
    metadata: {
        src: ['metadata/**/*.json']
    },
    config: {
        src: ['config/**/*.json']
    }
};
