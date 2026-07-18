import { createWpViteConfig } from 'pressbooks-build-tools';
import { resolve } from 'path';

export default createWpViteConfig({
	input: {
		'excalibur': resolve(__dirname, 'assets/src/scripts/excalibur.js'),
	},
	outDir: 'assets/dist',
});
