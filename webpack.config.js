const path = require("path");
const { SourceMapDevToolPlugin } = require("webpack");

module.exports = {
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  entry: ['./src/js/wordland.js', './src/scss/wordland.scss'],
  output: {
    path: path.resolve(__dirname, 'assets'),
    filename: process.env.NODE_ENV === 'production' ? 'js/wordland-reactjs-ui.min.js' : 'js/wordland-reactjs-ui.js',
  },
  module: {
    rules: [
      {
				test: /\.scss$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: process.env.NODE_ENV === 'production' ? 'css/[name].min.css' : 'css/[name].css',
						}
					},
					{
						loader: 'extract-loader'
					},
					{
						loader: 'css-loader?-url'
					},
					{
						loader: 'postcss-loader'
					},
					{
						loader: 'sass-loader'
					}
				]
			},
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components)/,
        loader: "babel-loader",
        options: {
          presets: ["@babel/env"],
          plugins: ['transform-class-properties']},
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"],
      },
    ],
  },
  resolve: { extensions: ["*", ".js", ".jsx"] },
  devServer: {
    contentBase: path.join(__dirname, "public/"),
    port: 3000,
    publicPath: "http://localhost:3000/assets/",
    hotOnly: true,
  },
  plugins: [
    new SourceMapDevToolPlugin({
      filename: "[file].map"
    }),
  ],
  externals: {
    react: 'React',
    'react-dom': 'ReactDOM',
  }
};
