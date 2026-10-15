/**
 * Assembles the demo site in dist/site: the landing page, the Playground blueprints and the
 * plugin and theme zips. Run `npm run package` first.
 *
 * The blueprints must point at absolute URLs, so the site's own address is filled in here:
 * SITE_URL when set, otherwise the Vercel production domain, otherwise GitHub Pages.
 */
import { copyFileSync, mkdirSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const root = new URL( '../..', import.meta.url ).pathname;
const source = join( root, 'tools/playground/site' );
const target = join( root, 'dist/site' );

const siteUrl = (
	process.env.SITE_URL ||
	( process.env.VERCEL_PROJECT_PRODUCTION_URL && `https://${ process.env.VERCEL_PROJECT_PRODUCTION_URL }` ) ||
	'https://yevhentiutiunnyk.github.io/hive-coworking'
).replace( /\/$/, '' );

rmSync( target, { recursive: true, force: true } );
mkdirSync( target, { recursive: true } );

for ( const file of readdirSync( source ) ) {
	writeFileSync( join( target, file ), readFileSync( join( source, file ), 'utf8' ).replaceAll( '__SITE_URL__', siteUrl ) );
}

for ( const zip of [ 'hive-core.zip', 'hive.zip' ] ) {
	copyFileSync( join( root, 'dist', zip ), join( target, zip ) );
}

console.log( `Demo site built in dist/site for ${ siteUrl }` );
