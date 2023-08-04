const MiniCssExtractPlugin = require('mini-css-extract-plugin');
var path = require('path');

// change these variables to fit your project
const jsPath = './js';
const cssPath = './css';
const outputPath = 'dist';
const entryPoints = {
    // 'app' is the output name, people commonly use 'bundle'
    // you can have more than 1 entry point
    'app.min': jsPath + '/app.js',
    'admin.min': jsPath + '/admin.js',
    'app': cssPath + '/app.scss',
    'admin': cssPath + '/admin.scss',
    'ultimate-member': cssPath + '/ultimate-member.scss',
    'ultimate-member.min': jsPath + '/ultimate-member.js',
    'woo-custom': cssPath + '/woo-custom.scss',
    'woo-custom.min': jsPath + '/woo-custom.js',
};

module.exports = {
    entry: entryPoints,
    output: {
        path: path.resolve(__dirname, outputPath),
        filename: '[name].js',
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css',
        }),

    ],
    module: {
        rules: [{
                test: /\.scss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    'sass-loader'
                ]
            },
            {
                test: /\.(jpg|jpeg|png|gif|woff|woff2|eot|ttf|svg)$/i,
                use: 'url-loader?limit=1024'
            }
        ]
    },
};
