const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
    entry: {
        memberships: [],
    },
    output: {
        path: path.resolve(__dirname, 'assets'),
        filename: '[name].js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: "babel-loader"
        },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader'
                },
                    {
                        loader: 'sass-loader'
                }
                ]
        }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: process.env.NODE_ENV === 'production' ? 'admin/css/[name].min.css' : 'admin/css/[name].css'
        })
    ]
}
