module.exports = {
  root: true,
  env: {
    node: true,
    browser: true,
  },
  ignorePatterns: ['dist/', 'node_modules/', 'public/service-worker.js'],
  extends: [
    'plugin:vue/vue3-essential',
    'eslint:recommended',
  ],
  parserOptions: {
    parser: '@babel/eslint-parser',
    ecmaVersion: 2020,
    sourceType: 'module',
  },
  rules: {
    'vue/no-dupe-keys': 'error',
    'no-dupe-keys': 'error',
    // 必要に応じて他のルールを追加
  },
};