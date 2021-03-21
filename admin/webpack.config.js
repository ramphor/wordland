module.exports = {
    mode: "production",
    entry: "./src/index.js",
    output: {
      path: __dirname,
      filename: "assets/js/wordland.js"
    },
    resolve: {
      extensions: [".js", ".marko"]
    },
    module: {
      rules: [
        {
          test: /\.marko$/,
          loader: "@marko/webpack/loader"
        }
      ]
    }
  };