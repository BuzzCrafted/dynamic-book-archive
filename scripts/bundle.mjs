/**
 * Writes bundle/<slug>/ with files required to run the theme in WordPress,
 * then zips it to bundle/<slug>.zip (single top-level folder for WP upload).
 */

import { spawnSync } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const THEME_SLUG = 'dynamic-book-archive';
const COPY_DIRS = ['inc', 'template-parts', 'assets', 'vendor'];

async function rmrf(target) {
	await fs.rm(target, { recursive: true, force: true });
}

const SKIP_NAMES = new Set(['.DS_Store', 'Thumbs.db']);

async function cpDir(src, dest) {
	await fs.mkdir(dest, { recursive: true });
	const entries = await fs.readdir(src, { withFileTypes: true });
	for (const entry of entries) {
		if (SKIP_NAMES.has(entry.name)) {
			continue;
		}
		const from = path.join(src, entry.name);
		const to = path.join(dest, entry.name);
		if (entry.isDirectory()) {
			await cpDir(from, to);
		} else if (entry.isFile()) {
			await fs.copyFile(from, to);
		}
	}
}

async function main() {
	const bundleRoot = path.join(root, 'bundle');
	const outDir = path.join(bundleRoot, THEME_SLUG);

	await rmrf(bundleRoot);
	await fs.mkdir(outDir, { recursive: true });

	const names = await fs.readdir(root, { withFileTypes: true });
	for (const entry of names) {
		if (!entry.isFile() || !entry.name.endsWith('.php')) {
			continue;
		}
		await fs.copyFile(path.join(root, entry.name), path.join(outDir, entry.name));
	}

	const rootCopyFiles = ['style.css', 'screenshot.png'];
	for (const name of rootCopyFiles) {
		const src = path.join(root, name);
		try {
			await fs.access(src);
		} catch {
			console.error(`Bundle failed: ${name} is missing at theme root.`);
			process.exit(1);
		}
		await fs.copyFile(src, path.join(outDir, name));
	}

	for (const dir of COPY_DIRS) {
		const src = path.join(root, dir);
		let st;
		try {
			st = await fs.stat(src);
		} catch {
			st = null;
		}
		if (!st?.isDirectory()) {
			if (dir === 'vendor') {
				console.error(
					'Bundle failed: vendor/ is missing. Run `composer install` (or `npm run bundle:composer`) from the theme root.'
				);
				process.exit(1);
			}
			continue;
		}
		await cpDir(src, path.join(outDir, dir));
	}

	const autoload = path.join(outDir, 'vendor', 'autoload.php');
	try {
		await fs.access(autoload);
	} catch {
		console.error('Bundle failed: vendor/autoload.php missing after copy.');
		process.exit(1);
	}

	const zipPath = path.join(bundleRoot, `${THEME_SLUG}.zip`);
	const zip = spawnSync('zip', ['-qr', zipPath, THEME_SLUG], {
		cwd: bundleRoot,
		encoding: 'utf8',
	});
	if (zip.error?.code === 'ENOENT') {
		console.warn('zip CLI not found; skipped .zip (folder install still works):', zip.error.message);
	} else if (zip.status !== 0) {
		console.error(zip.stderr || zip.stdout || `zip exited ${zip.status}`);
		process.exit(zip.status ?? 1);
	}

	console.log(`Bundle ready: ${outDir}`);
	if (!zip.error) {
		console.log(`Zip (WP upload): ${zipPath}`);
	}
}

main().catch((err) => {
	console.error(err);
	process.exit(1);
});
