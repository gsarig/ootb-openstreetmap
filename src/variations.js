// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

// Unregister the `custom-fields` variation if the Location setting is disabled.
if (!ootbGlobal?.options?.geodata) {
	wp.blocks.unregisterBlockVariation('ootb/openstreetmap', 'custom-fields');
}
