const path = require( 'path' );
const glob = require( 'glob' );
const fs = require( 'fs' );
const defaultConfig = require( "@wordpress/scripts/config/webpack.config" );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CssMinimizerPlugin = require( "css-minimizer-webpack-plugin" );
const { hasBabelConfig } = require( '@wordpress/scripts/utils' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const UnminifiedWebpackPlugin = require( 'unminified-webpack-plugin' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const CopyWebpackPlugin = require('copy-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

const BUILD_DIR = path.resolve( __dirname, 'assets' );

const filename = ext => isProduction ? ext + '/[name].min.' + ext : ext + '/[name].min.' + ext;

module.exports = {
	...defaultConfig,
	mode,
	devtool: ! isProduction ? 'source-map' : false,
	//devtool:      ! isProduction ? false : false,
	entry: {
		main: path.resolve( process.cwd(), 'src/js', 'main.js' ),
		"ast-admin-script":  path.resolve( process.cwd(), 'src/js', 'admin-script.js' ),
		"ast-admin-style":  path.resolve( process.cwd(), 'src/scss', 'admin-style.scss' ),
	},
	output: {
		filename: filename( 'js' ),
		path: BUILD_DIR,
		clean: true,
		chunkFilename: '[name].js'
	},
	optimization: {
		splitChunks: {
			cacheGroups: {
				default: false, // Отключаем дефолтное разделение
				vendors: false, // Отключаем разделение vendor-кода
			},
		},
		runtimeChunk: false, // Отключаем runtime-чанк
		minimize: true,
		minimizer: [
			new CssMinimizerPlugin( {
				minimizerOptions: {
					preset: [
						"default",
						{ "discardComments": { "removeAll": true } }
					]
				},
			} ),
			new TerserPlugin( {
				extractComments: false,
				terserOptions: {
					format: {
						comments: false,
					},
				},
				exclude: /sprite-svg\.js/,
			} ),
		]
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [
					require.resolve( 'babel-loader' ),
					{
						loader: require.resolve( 'babel-loader' ),
						options: {
							cacheDirectory: process.env.BABEL_CACHE_DIRECTORY || true,
							presets: [
								require.resolve( '@wordpress/babel-preset-default' ),
								require.resolve( '@babel/preset-env' ),
							],
						},
					},
				],
			},
			{
				test: /\.css$/i,
				use: [ "style-loader", "css-loader" ],
			},
			{
				test: /\.s[ac]ss$/i,
				exclude: /node_modules/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
					},
					{
						loader: 'css-loader',
						options: {
							sourceMap: ! isProduction,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							sourceMap: !isProduction,
							postcssOptions: {
								plugins: [
									['autoprefixer', {
										overrideBrowserslist: ['last 2 versions', '> 1%', 'ie >= 11']
									}]
								]
							}
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: ! isProduction,
							implementation: require( 'sass' ),
							sassOptions: {
								includePaths: [
									path.resolve(__dirname, 'src/scss'),
									path.resolve(__dirname, 'node_modules'),
								],
							},
						},
					},
				],
			},
			{
				test: /\.(?:ico|gif|png|jpg|jpeg|webp|svg)$/i,
				include: path.resolve( __dirname, 'src/images' ),
				type: 'asset/resource',
				generator: {
					filename: "images/[name][ext]",
				},
			},
		],
	},
	plugins: [
		new RemoveEmptyScriptsPlugin(),
		new MiniCssExtractPlugin( {
			filename: filename( 'css' ),
		} ),
		new UnminifiedWebpackPlugin( {
			exclude: [ /sprite-svg/ ],
		} )
	],

	externals: {
		jquery: 'jQuery',
	}
}
