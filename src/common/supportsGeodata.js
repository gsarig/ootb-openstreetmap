// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

export default function supportsGeodata(postType = null) {
    const validPostTypes = ootbGlobal?.options?.geo_post_types ? Object.keys(ootbGlobal.options.geo_post_types) : [];
    return postType === null || !ootbGlobal?.options?.geodata || !validPostTypes.includes(postType);
}
