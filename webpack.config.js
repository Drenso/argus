const path = require('path');
const fs = require('fs');
const Encore = require('@symfony/webpack-encore');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    // Frontend entry point
    .addEntry('layout', './assets/js/layout.js')
    .addEntry('app', './assets/vue/app.ts')
    .addEntry('minimal', './assets/css/minimal.scss')

    // Enable Vue load
    .enableVueLoader(() => {
    }, {runtimeCompilerBuild: false})

    .configureMiniCssExtractPlugin(() => {
    }, (pluginConfig) => {
      pluginConfig.ignoreOrder = true;
    })

    // Do not load images as ES module
    .configureImageRule({
      type: 'asset/resource',
    })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild(['*/**', '!.gitkeep'])
    .enableSourceMaps(process.env.DISABLE_SOURCE_MAPS !== 'true')
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // Polyfill and transpilation options
    .configureBabel(null, {
      includeNodeModules: ['@drenso/vue-frontend-shared'],
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    .enableBabelTypeScriptPreset({})

    // Enable post css processing
    .enablePostCssLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // Provide popper global var for bootstrap
    .autoProvideVariables({
      Popper: ['popper.js', 'default']
    })

    .addLoader({
      test: /\b(frontend|messages|validators|drenso_shared)\+intl-icu\.(.+)\.yml$/,
      loader: 'messageformat-loader',
      type: 'javascript/auto'
    })

    .addPlugin(new MomentLocalesPlugin())
    .addPlugin(new ForkTsCheckerWebpackPlugin({
      typescript: {
        extensions: {
          vue: true,
        },
      },
    }))

    .configureDevServerOptions(options => {
      options.allowedHosts = 'all';
      options.https = {
        cert: '/etc/apache2/ssl/drenso.dev/fullchain.pem',
        key: '/etc/apache2/ssl/drenso.dev/privkey.pem',
      };
    })

    // Enable the webpack build cache
    .enableBuildCache({
      // object of "buildDependencies"
      // https://webpack.js.org/configuration/other-options/#cachebuilddependencies
      // __filename means that changes to webpack.config.js should invalidate the cache
      config: [__filename],
    })
;

const webpackConfig = Encore.getWebpackConfig();

// Add aliases
webpackConfig.resolve.alias['@'] = path.resolve(__dirname, 'assets/js');
webpackConfig.resolve.alias['@fos'] = path.resolve(__dirname, 'vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js');
webpackConfig.resolve.alias['@trans'] = path.resolve(__dirname, 'translations');
webpackConfig.resolve.alias['@drensoTrans'] = path.resolve(__dirname, 'vendor/drenso/symfony-shared/src/Resources/translations');

module.exports = webpackConfig;
