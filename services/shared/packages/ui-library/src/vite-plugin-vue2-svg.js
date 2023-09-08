const { readFileSync } = require('fs');
const { basename } = require('path');
const { optimize } = require('svgo');
const { compileTemplate, parse } = require('@vue/component-compiler-utils');
const compiler = require('vue-template-compiler');
const svgoConfig = require('./svgo.config');
// this file is shamelessly copied from https://github.com/pakholeung37/vite-plugin-vue2-svg
// we have added slightly improved the working of the plugin to support our usecases as well
// currently there're 3 ways to use SVGs: using the asset URL, Importing Vue components (?vue) and import raw svg HTML (?raw)

function compileSvg(svg, id) {
    const template = parse({
        source: `
      <template>
        ${svg}
      </template>
    `,
        compiler: compiler,
        filename: `${basename(id)}.vue`,
    }).template;

    if (!template) return '';

    const result = compileTemplate({
        compiler: compiler,
        source: template.content,
        filename: `${basename(id)}.vue`,
    });

    return `
    ${result.code}
    export default {
      render: render,
    }
  `;
}

function optimizeSvg(content, svgoConfig) {
    const result = optimize(content, svgoConfig);

    if ('data' in result) {
        return result.data;
    }

    throw new Error(`[vite-plugin-vue2-svg] cannot optimize SVG ${svgoConfig.path}`);
}

function vueSvg() {
    const svgRegex = /\.svg$/;

    return {
        name: 'vite-plugin-vue2-svg',
        async transform(_source, id) {
            if (/\?raw/.test(id)) {
                return null;
            }
            if (/\?vue/.test(id)) {
                const fname = id.replace(/\?.*$/, '');
                const isMatch = svgRegex.test(fname);
                if (isMatch) {
                    const code = readFileSync(fname, { encoding: 'utf-8' });
                    let svg = await optimizeSvg(code, { path: fname, ...svgoConfig });
                    if (!svg) throw new Error(`[vite-plugin-vue2-svg] fail to compile ${id}`);
                    svg = svg.replace('<svg', '<svg v-on="$listeners"');
                    const result = compileSvg(svg, fname);

                    return {
                        code: result,
                        map: null, // Prevent missing sourcemap warning
                    };
                }
                return null;
            }
            return;
        },
    };
}

/**
 * There are some issues with importing TS files into the vite.config from another package.
 * Thats while this plugin in written as a commonJS module.
 * @see: https://github.com/vitejs/vite/issues/5370
 */
module.exports = { vueSvg };
