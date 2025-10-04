const webpack = require('webpack');
const path = require('path');
const postcssConfig = require('./postcss.config');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const TerserJSPlugin = require('terser-webpack-plugin');
const ESLintPlugin = require('eslint-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const RemovePlugin = require('remove-files-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
    mode: process.env.NODE_ENV,
    infrastructureLogging: { debug: true },
    watchOptions: {
        aggregateTimeout: 400, // webpack5で2回コンパイルされるバグの暫定対応: https://github.com/webpack/webpack/issues/15431
    },
    devtool: !isProduction ? 'inline-source-map' : false,
    stats: {
        loggingDebug: ['sass-loader'],
    },
    entry: {
        'assets/js/main': path.resolve(__dirname, 'assets/src/js/index.js'),
        'assets/js/editor': path.resolve(__dirname, 'assets/src/js/editor.js'),
        'assets/js/inspector': path.resolve(__dirname, 'assets/src/js/inspector.js'),
        'editor-style': path.resolve(__dirname, 'assets/src/sass/wordpress/editor.scss'),
        style: path.resolve(__dirname, 'assets/src/sass/index.scss'),
    },
    output: {
        path: path.resolve(__dirname),
        publicPath: '/wp-content/themes/' + path.basename(path.resolve(__dirname)) + '/assets/',
        filename: '[name].js',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: [
                    {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                [
                                    '@babel/preset-env',
                                    {
                                        targets: {
                                            ie: 11,
                                        },
                                        useBuiltIns: 'usage',
                                        corejs: 3,
                                    },
                                ],
                                '@babel/preset-react',
                            ],
                            plugins: [['@babel/plugin-transform-runtime']],
                        },
                    },
                ],
                exclude: /node_modules\/(?!(core-module)\/).*/,
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            url: true,
                            sourceMap: !isProduction,
                        },
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            ...postcssConfig,
                            sourceMap: !isProduction,
                        },
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: !isProduction,
                            implementation: require('sass'),
                        },
                    },
                ],
                exclude: /node_modules\/(?!(core-module)\/).*/,
            },
            {
                test: /\.(woff(2)?|ttf|eot)(\?v=\d+\.\d+\.\d+)?$/,
                type: 'asset/resource',
                generator: {
                    filename: 'fonts/[name][ext]',
                    outputPath: 'assets/',
                },
            },
            {
                test: /\.(jpe?g|png|gif|bmp|svg|webp)$/,
                type: 'asset/resource',
                generator: {
                    filename: 'images/[name][ext]',
                    outputPath: 'assets/',
                    emit: false,
                },
            },
        ],
    },
    plugins: [
        new webpack.ProgressPlugin(),
        new CleanWebpackPlugin({
            cleanOnceBeforeBuildPatterns: ['assets/**', '*.css', '!assets', '!assets/src/**'],
            dry: !isProduction,
            verbose: true,
        }),
        new RemovePlugin({
            after: {
                root: '.',
                test: [
                    {
                        folder: 'assets/js',
                        method: (absoluteItemPath) => {
                            return new RegExp(/\.css$/, 'm').test(absoluteItemPath);
                        },
                        recursive: false,
                    },
                ],
                emulate: false,
            },
        }),
        new ESLintPlugin({
            fix: false,
            failOnError: false,
        }),
        new FixStyleOnlyEntriesPlugin({
            silent: false,
        }),
        new MiniCssExtractPlugin({
            filename: '[name].css',
        }),
        new CopyPlugin({
            patterns: [
                {
                    from: 'assets/src/images/*',
                    // eslint-disable-next-line no-unused-vars
                    to({ context, absoluteFilename }) {
                        return 'assets/images/[name][ext]';
                    },
                    globOptions: {
                        ignore: ['**/*.sh'],
                    },
                    noErrorOnMissing: true,
                },
                {
                    from: 'assets/src/videos/*',
                    // eslint-disable-next-line no-unused-vars
                    to({ context, absoluteFilename }) {
                        return 'assets/videos/[name][ext]';
                    },
                    noErrorOnMissing: true,
                },
                {
                    from: 'assets/src/js/vendor/*',
                    // eslint-disable-next-line no-unused-vars
                    to({ context, absoluteFilename }) {
                        return 'assets/js/vendor/[name][ext]';
                    },
                    noErrorOnMissing: true,
                },
                {
                    from: 'assets/src/json/*',
                    // eslint-disable-next-line no-unused-vars
                    to({ context, absoluteFilename }) {
                        return 'assets/json/[name][ext]';
                    },
                    noErrorOnMissing: true,
                },
            ],
        }),
    ].filter(Boolean),
    optimization: {
        minimizer: [
            new TerserJSPlugin({
                extractComments: false,
                terserOptions: {
                    compress: {
                        drop_console: true,
                    },
                },
            }),
            new CssMinimizerPlugin({
                minimizerOptions: {
                    preset: ['default', { calc: false }],
                },
            }),
        ],
    },
};
