#!/usr/bin/env node
'use strict';

const fs = require( 'fs' );
const path = require( 'path' );
const { spawnSync } = require( 'child_process' );

const BLUEPRINT = path.join( __dirname, '..', '.wordpress-org', 'blueprints', 'blueprint.json' );
const SCHEMA_URL = 'https://playground.wordpress.net/blueprint-schema.json';

async function main() {
	let failed = false;

	// 1. JSON syntax
	let blueprint;
	try {
		blueprint = JSON.parse( fs.readFileSync( BLUEPRINT, 'utf8' ) );
		console.log( 'OK  JSON syntax' );
	} catch ( e ) {
		console.error( 'ERR JSON syntax —', e.message );
		process.exit( 1 );
	}

	// 2. Step property validation against the Playground schema.
	//    Uses oneOf definitions to extract allowed properties per step type,
	//    then flags any unexpected property on each step.
	//    Skipped gracefully if the network is unavailable.
	try {
		const res = await fetch( SCHEMA_URL );
		if ( ! res.ok ) throw new Error( `HTTP ${ res.status }` );
		const schema = await res.json();

		// Build a map of stepName → allowed property names from schema definitions.
		const stepSchemas = {};
		const oneOf = schema.definitions?.StepDefinition?.oneOf || [];
		for ( const def of oneOf ) {
			const stepConst = def?.properties?.step?.const;
			if ( stepConst && def.properties ) {
				stepSchemas[ stepConst ] = Object.keys( def.properties );
			}
		}

		let schemaOk = true;
		blueprint.steps.forEach( ( step, i ) => {
			const allowed = stepSchemas[ step.step ];
			if ( ! allowed ) {
				console.error( `ERR schema steps[${ i }]: unknown step type "${ step.step }"` );
				schemaOk = false;
				return;
			}
			const unexpected = Object.keys( step ).filter( ( k ) => ! allowed.includes( k ) );
			if ( unexpected.length ) {
				console.error(
					`ERR schema steps[${ i }] (${ step.step }): unexpected properties: ${ unexpected.join( ', ' ) }`
				);
				schemaOk = false;
			}
		} );

		if ( schemaOk ) console.log( 'OK  blueprint schema' );
		else failed = true;
	} catch ( e ) {
		console.warn( 'SKIP schema validation —', e.message );
	}

	// 3. PHP syntax check for every embedded PHP script.
	const phpSteps = blueprint.steps.filter(
		( s ) => s.step === 'writeFile' && s.path?.endsWith( '.php' ) && s.data
	);
	for ( const step of phpSteps ) {
		const tmp = `/tmp/ootb-blueprint-${ Date.now() }.php`;
		fs.writeFileSync( tmp, step.data );
		const result = spawnSync( 'php', [ '-l', tmp ], { encoding: 'utf8' } );
		fs.unlinkSync( tmp );
		if ( result.status === 0 ) {
			console.log( `OK  PHP syntax (${ step.path })` );
		} else {
			console.error( `ERR PHP syntax (${ step.path }) —`, result.stdout.trim() );
			failed = true;
		}
	}

	if ( failed ) process.exit( 1 );
}

main().catch( ( e ) => {
	console.error( 'ERR', e.message );
	process.exit( 1 );
} );
