// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable


import ShapePolygon from "./ShapePolygon";
import ShapePolyline from "./ShapePolyline";

export default function Shapes({props}) {
	const {
		attributes: {
			mapType,
			markers,
			shapeColor,
			shapeWeight,
			isDraggingMarker,
		},
	} = props;

	const styles = { // Available options: https://leafletjs.com/reference.html#path
		fillColor: shapeColor,
		color: shapeColor,
		weight: shapeWeight
	}

	if ('polygon' === mapType && true !== isDraggingMarker && markers.length) {
		return (
			<ShapePolygon props={props} styles={styles}/>
		);
	} else if ('polyline' === mapType && true !== isDraggingMarker && markers.length) {
		return (
			<ShapePolyline props={props} styles={styles}/>
		);
	} else {
		return null;
	}
}
