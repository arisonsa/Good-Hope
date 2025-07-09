module.exports = function (api) {
  api.cache(true);

  const presets = [
    '@babel/preset-env',
    '@babel/preset-typescript',
  ];

  const plugins = [
    // StyleX Babel Plugin. Required to use StyleX.
    // Options:
    //   - If you are using StyleX version < 0.5:
    //     ['@stylexjs/babel-plugin', { stylexSheetName: '<>' }]
    //   - If you are using StyleX version >= 0.5:
    //     '@stylexjs/babel-plugin'
    //   - Other options:
    //     dev: boolean,
    //     test: boolean,
    //     unstable_moduleResolution: { type: 'commonJS' | 'haste', rootDir: string }
    //     runtimeInjection: boolean | { classNamePrefix?: string, stylexSheetName?: string }
    // See https://stylexjs.com/docs/api/babel-plugin/
    [
      '@stylexjs/babel-plugin',
      {
        // Assuming StyleXJS >= 0.5.x. For older versions, you might need different config.
        dev: process.env.NODE_ENV === 'development', // Set based on your environment
        // runtimeInjection: true, // For DevTools and HMR, might need @stylexjs/dev-runtime
        // Set 'stylexSheetName' if you want to control the generated CSS file name/structure.
        // e.g., stylexSheetName: 'stylex', // this would create `stylex.css`
        // By default, it might create multiple CSS files or use a hash.
        // For simplicity with Laravel Mix, aiming for a predictable output name is good.
        // However, the plugin often outputs CSS alongside JS files that then need to be imported.
        // The exact mechanism depends on the bundler integration.
        // For Webpack, you typically import the generated CSS from the JS file where styles are defined.
        // e.g. import './MyComponent.stylex.css';
        unstable_moduleResolution: {
            type: 'commonJS', // Or 'haste' if using Meta's internal module system
            rootDir: process.cwd(), // Project root
        },
      },
    ],
    // Other Babel plugins for Lit, if necessary (e.g., decorators if used, though Lit prefers standard JS)
  ];

  return {
    presets,
    plugins,
  };
};
