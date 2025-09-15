/** @type { import('@storybook/html-vite').StorybookConfig } */
const config = {
  "stories": [
    "../../components/**/*.mdx",
    "../../components/**/*.stories.@(js|jsx|mjs|ts|tsx)"
  ],
  "addons": [
    "@storybook/addon-docs"
  ],
  "framework": {
    "name": "@storybook/html-vite",
    "options": {}
  },
  viteFinal: async (config) => {
    config.build = config.build || {};
    config.build.rollupOptions = config.build.rollupOptions || {};
    config.build.rollupOptions.external = [
      'twig',
      'drupal-attribute',
      'drupal-twig-extensions',
      'drupal-twig-extensions/twig',
    ];

    config.optimizeDeps = config.optimizeDeps || {};
    config.optimizeDeps.exclude = [
      'twig',
      'drupal-attribute',
      'drupal-twig-extensions',
      'drupal-twig-extensions/twig',
    ];

    return config;
  },
};
export default config;