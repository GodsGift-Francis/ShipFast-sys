import base from './vite.config.js';
export default {...base, resolve: {preserveSymlinks: true}, esbuild: false, build: {minify: false, cssMinify: false}};
