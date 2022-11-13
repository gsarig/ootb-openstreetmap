import centerMap from './Helpers/centerMap';
import getIconDeprecated from './Deprecated/getIconDeprecated';
import getIcon from './Helpers/getIcon';

const deprecated = [
	{
		attributes: {
			mapHeight: {
				type: 'integer',
				default: 400,
			},
			markers: {
				type: 'array',
				default: [],
			},
			zoom: {
				type: 'integer',
				default: 8,
			},
			minZoom: {
				type: 'integer',
				default: 2,
			},
			maxZoom: {
				type: 'integer',
				default: 18,
			},
			dragging: {
				type: 'boolean',
				default: true,
			},
			touchZoom: {
				type: 'boolean',
				default: true,
			},
			doubleClickZoom: {
				type: 'boolean',
				default: true,
			},
			scrollWheelZoom: {
				type: 'boolean',
				default: true,
			},
			defaultIcon: {
				type: 'object',
				default: null,
			},
			bounds: {
				type: 'array',
				default: [
					[37.97155174977503, 23.72656345367432]
				],
			},
			provider: {
				type: 'string',
				default: 'openstreetmap',
			},
		},
		save(props) {
			const {
				className,
				attributes: {
					mapHeight,
					markers,
					zoom,
					minZoom,
					maxZoom,
					dragging,
					touchZoom,
					doubleClickZoom,
					scrollWheelZoom,
					provider,
				},
			} = props;

			// noinspection JSXNamespaceValidation
			return markers ? (
				<div className={className}>
					<div className="ootb-openstreetmap--map"
						 data-provider={provider}
						 data-markers={escape(JSON.stringify(markers))} // Escape because of the potential HTML in the output.
						 data-bounds={JSON.stringify(centerMap(props))}
						 data-zoom={zoom}
						 data-minzoom={minZoom}
						 data-maxzoom={maxZoom}
						 data-dragging={dragging}
						 data-touchzoom={touchZoom}
						 data-doubleclickzoom={doubleClickZoom}
						 data-scrollwheelzoom={scrollWheelZoom}
						 data-marker={escape(JSON.stringify(getIcon(props)))}
						 style={
							 {
								 height: mapHeight + 'px'
							 }
						 }
					>
					</div>
				</div>
			) : null;
		}
	},
	{
		attributes: {
			mapHeight: {
				type: 'integer',
				default: 400,
			},
			markers: {
				type: 'array',
				default: [],
			},
			zoom: {
				type: 'integer',
				default: 8,
			},
			minZoom: {
				type: 'integer',
				default: 2,
			},
			maxZoom: {
				type: 'integer',
				default: 18,
			},
			dragging: {
				type: 'boolean',
				default: true,
			},
			touchZoom: {
				type: 'boolean',
				default: true,
			},
			doubleClickZoom: {
				type: 'boolean',
				default: true,
			},
			scrollWheelZoom: {
				type: 'boolean',
				default: true,
			},
			defaultIcon: {
				type: 'object',
				default: null,
			},
			bounds: {
				type: 'array',
				default: [
					[37.97155174977503, 23.72656345367432]
				],
			},
		},
		supports: {
			align: ['wide', 'full'],
		},
		save(props) {
			const {
				className,
				attributes: {
					mapHeight,
					markers,
					zoom,
					minZoom,
					maxZoom,
					dragging,
					touchZoom,
					doubleClickZoom,
					scrollWheelZoom,
				},
			} = props;
			return markers ? (
				<div className={className}>
					<div className="ootb-openstreetmap--map"
						 data-markers={escape(JSON.stringify(markers))} // Escape because of the potential HTML in the output.
						 data-bounds={JSON.stringify(centerMap(props))}
						 data-zoom={zoom}
						 data-minzoom={minZoom}
						 data-maxzoom={maxZoom}
						 data-dragging={dragging}
						 data-touchzoom={touchZoom}
						 data-doubleclickzoom={doubleClickZoom}
						 data-scrollwheelzoom={scrollWheelZoom}
						 data-marker={escape(JSON.stringify(getIcon(props)))}
						 style={
							 {
								 height: mapHeight + 'px'
							 }
						 }
					>
					</div>
				</div>
			) : null;
		}
	},
	{
		attributes: {
			mapHeight: {
				type: 'integer',
				default: 400,
			},
			markers: {
				type: 'array',
				default: [],
			},
			zoom: {
				type: 'integer',
				default: 8,
			},
			minZoom: {
				type: 'integer',
				default: 2,
			},
			maxZoom: {
				type: 'integer',
				default: 18,
			},
			dragging: {
				type: 'boolean',
				default: true,
			},
			touchZoom: {
				type: 'boolean',
				default: true,
			},
			doubleClickZoom: {
				type: 'boolean',
				default: true,
			},
			scrollWheelZoom: {
				type: 'boolean',
				default: true,
			},
			defaultIcon: {
				type: 'object',
				default: null,
			},
			bounds: {
				type: 'array',
				default: [
					[37.97155174977503, 23.72656345367432]
				],
			},
		},
		supports: {
			align: ['wide', 'full'],
		},
		save(props) {
			const {
				className,
				attributes: {
					mapHeight,
					markers,
					zoom,
					minZoom,
					maxZoom,
					dragging,
					touchZoom,
					doubleClickZoom,
					scrollWheelZoom,
				},
			} = props;
			return markers ? (
				<div className={className}>
					<div className="ootb-openstreetmap--map"
						 data-markers={escape(JSON.stringify(markers))} // Escape because of the potential HTML in the output.
						 data-bounds={JSON.stringify(centerMap(props))}
						 data-zoom={zoom}
						 data-minzoom={minZoom}
						 data-maxzoom={maxZoom}
						 data-dragging={dragging}
						 data-touchzoom={touchZoom}
						 data-doubleclickzoom={doubleClickZoom}
						 data-scrollwheelzoom={scrollWheelZoom}
						 data-marker={escape(JSON.stringify(getIconDeprecated(props)))}
						 style={
							 {
								 height: mapHeight + 'px'
							 }
						 }
					>
					</div>
				</div>
			) : null;
		}
	}
]
export default deprecated;
