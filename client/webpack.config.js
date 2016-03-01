module.exports = {
    entry:  './src',
    output: {
        path:     'builds',
        filename: 'bundle.js',
    },
    module: {
      loaders: [
        {
          test: /\.js?$/,
          exclude: /node_modules/,
          loaders: ['babel?presets[]=es2015']
        },
        {
          test: /\.json$/,
          loader: 'json-loader'
        },
      ],
  },
};
