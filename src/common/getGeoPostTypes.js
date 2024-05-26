// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

export default function getGeoPostTypes() {
    const postTypes = ootbGlobal?.postTypes;
    const geoPostTypes = ootbGlobal?.options?.geo_post_types ? Object.keys(ootbGlobal.options.geo_post_types) : [];
    return postTypes.filter(item => geoPostTypes.includes(item.value));
}
