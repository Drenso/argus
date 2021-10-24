module.exports = {
  'extends': [
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/recommended',
  ],
  'parser': 'vue-eslint-parser',
  'parserOptions': {
    'extraFileExtensions': ['.vue'],
    'parser': '@typescript-eslint/parser',
    'sourceType': 'module',
  },
  'plugins': [
    'eslint-plugin-import',
    'eslint-plugin-jsdoc',
    'eslint-plugin-prefer-arrow',
    '@typescript-eslint',
  ],
  'rules': {
    // Order vue attributes alphabetically
    'vue/attributes-order': ['warn', {
      'alphabetical': true,
    }],
    // Ensure 2 indent is used
    'vue/html-indent': ['warn', 2, {
      'attribute': 2,
    }],
    // Never place the closing `>` on a newline
    'vue/html-closing-bracket-newline': ['error', {
      'singleline': 'never',
      'multiline': 'never',
    }],
    // Force `<Component/>` without space(s) before `/>`
    'vue/html-closing-bracket-spacing': ['warn', {
      'selfClosingTag': 'never',
    }],
    // Configure custom modifiers
    'vue/valid-v-on': ['error', {
      'modifiers': ['at', 'hash'],
    }],
    // Required to not mark bootstrap-vue table slot syntax as invalid
    'vue/valid-v-slot': ['error', {
      'allowModifiers': true,
    }],
    'max-len': ['warn', {
      'code': 140,
      'ignoreTrailingComments': true,
    }],
    // Mark as warning to not block during dev work, and allow with description
    '@typescript-eslint/ban-ts-comment': ['warn', {
      'ts-ignore': 'allow-with-description',
    }],
    // There are to many issues with the ident plugin: https://github.com/typescript-eslint/typescript-eslint/issues/1824
    '@typescript-eslint/indent': 'off',
    // Only allow single quotes
    '@typescript-eslint/quotes': [
      'warn',
      'single',
    ],
    // Ensure a return type is set on all functions
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/explicit-function-return-type': ['warn', {
      allowExpressions: true,
      allowTypedFunctionExpressions: true,
      allowHigherOrderFunctions: true,
      allowDirectConstAssertionInArrowFunctions: true,
      allowConciseArrowFunctionExpressionsStartingWithVoid: true,
    }],
  },
};
