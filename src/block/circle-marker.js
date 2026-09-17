( function() {
	if ( typeof L === 'undefined' || typeof L.Map === 'undefined' ) {
		return;
	}

	function getRadius( container ) {
		const radius = parseInt( container?.dataset?.circleradius || container?.dataset?.circleradius, 10 );
		return Number.isFinite( radius ) && radius > 0 ? radius : 8;
	}

	function getCenter( container ) {
		try {
			const bounds = JSON.parse( container?.dataset?.bounds || 'null' );
			if ( Array.isArray( bounds ) && bounds.length >= 2 && bounds[0] != null && bounds[1] != null ) {
				return [ parseFloat( bounds[0] ), parseFloat( bounds[1] ) ];
			}
		} catch ( e ) {}

		try {
			const markers = JSON.parse( container?.dataset?.markers || 'null' );
			if ( Array.isArray( markers ) && markers.length > 0 && markers[0].lat != null && markers[0].lng != null ) {
				return [ parseFloat( markers[0].lat ), parseFloat( markers[0].lng ) ];
			}
		} catch ( e ) {}

		return null;
	}

	function getStyle( container ) {
		try {
			const style = JSON.parse( container?.dataset?.shapestyle || '{}' );
			return {
				color: style.color || '#008EFF',
				fillColor: style.fillColor || style.color || '#008EFF',
				weight: style.weight || 3,
				fillOpacity: 0.2,
				opacity: 1
			};
		} catch ( e ) {
			return { color: '#008EFF', fillColor: '#008EFF', weight: 3, fillOpacity: 0.2, opacity: 1 };
		}
	}

	function addCircleMarker( map ) {
		const container = map.getContainer();
		if ( !container || container.dataset.maptype !== 'circle' ) {
			return;
		}

		const center = getCenter( container );
		if ( !center ) {
			return;
		}

		L.circleMarker( center, {
			radius: getRadius( container ),
			pathOptions: getStyle( container )
		} ).addTo( map );
	}

	const originalInitialize = L.Map.prototype.initialize;
	L.Map.prototype.initialize = function( container, options ) {
		originalInitialize.call( this, container, options );
		if ( container && container.dataset && container.dataset.maptype === 'circle' ) {
			this.once( 'ready', () => addCircleMarker( this ) );
		}
	};
} )();
