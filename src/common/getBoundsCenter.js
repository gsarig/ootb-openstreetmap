export default function getBoundsCenter(arr) {
	const x = arr.map(xy => xy[0]);
	const y = arr.map(xy => xy[1]);
	const cx = (Math.min(...x) + Math.max(...x)) / 2;
	const cy = (Math.min(...y) + Math.max(...y)) / 2;
	return cx && !isNaN(cx) && cy && !isNaN(cy) ? [cx, cy] : null;
}
