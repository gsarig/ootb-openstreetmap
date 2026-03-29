// noinspection NpmUsedModulesInstalled,NpmUsedModulesInstalled

import {useState} from 'react';
import {useMapEvents} from 'react-leaflet/hooks';
import {isMobile, isSafari} from 'react-device-detect';
import getBounds from '../Helpers/getBounds';
import {useEffect, useRef} from '@wordpress/element';
import getMarkerFromElelement from '../../common/getMarkerFromElelement';

export default function MapEvents({addingMarker, setAddingMarker, props}) {
    const {
        attributes: {
            mapObj,
            markers,
            zoom,
            showDefaultBounds,
            shouldUpdateZoom,
            shouldUpdateBounds,
            isDraggingMarker,
            searchResults,
        },
        setAttributes
    } = props;
    const [searchCount, setSearchCount] = useState(0);
    const firstRender = useRef(true);
    const timeout = 300;
    let delay;
    const isClicking = (e) => {
        const elementClicked = e.originalEvent.target.nodeName.toLowerCase();
        if ('div' === elementClicked) {
            setAttributes({isDraggingMarker: false});
        }
        if (false === isDraggingMarker) {
            if (isMobile && !isSafari) {
                setAddingMarker(' pinning');
            } else {
                delay = setTimeout(function () {
                    setAddingMarker(' pinning');
                    setTimeout(function () { // If hangs for too long, stop it.
                        setAddingMarker('');
                    }, timeout * 3);
                }, timeout);
            }
        }
    }

    const isDragging = () => {
        clearTimeout(delay);
        setAddingMarker('');
        setAttributes({isDraggingMarker: false});
    }

    const stopHovering = () => {
        setAttributes({
            isDraggingMarker: false,
        });
    }

    const addMarker = (e) => {
        clearTimeout(delay);
        if (!!addingMarker) {
            const newMarker = getMarkerFromElelement({searchCount, setSearchCount, searchResults}, e);
            setAttributes({
                markers: [
                    ...markers,
                    newMarker,
                ],
            });
            setAddingMarker('');
            getBounds(props, newMarker, e.target);
            setTimeout(function () {
                setAttributes({isDraggingMarker: false});
            }, timeout * 2);
        }
    }
    const changeZoom = (e) => {
        setAttributes({zoom: e.zoom});
    }
    const closePopupActions = () => {
        setAttributes({isDraggingMarker: false});
    }
    const map = useMapEvents({
        // Available events: https://leafletjs.com/reference.html#evented
        mouseup: (e) => {
            addMarker(e);
        },
        mousedown: (e) => {
            isClicking(e);
        },
        drag: (e) => {
            isDragging(e);
        },
        mouseout: (e) => {
            stopHovering(e);
        },
        zoomanim: (e) => {
            changeZoom(e);
        },
        popupclose: () => {
            closePopupActions();
        }
    });
    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;
            return;
        }
        if ('undefined' !== typeof map && ('undefined' === typeof mapObj || !mapObj.length || mapObj !== map)) {
            setAttributes({mapObj: map});
        }
    }, [markers]);
    useEffect(() => {
        // The block's JavaScript runs in the parent frame, but in apiVersion 3 the
        // block's DOM (including the block wrapper and Leaflet map container) is
        // rendered inside an iframe.
        const container = map.getContainer();
        const iframeDoc = container.ownerDocument;
        const isInsideIframe = iframeDoc !== document;

        // In apiVersion 3, useBlockProps sets draggable="true" on the block wrapper.
        // Prevent HTML5 DnD from triggering when the user presses and moves the mouse
        // inside the map. Use capture phase on iframeDoc so our handler runs before
        // Gutenberg's block-drag listener on the block wrapper element.
        let mouseDownInMap = false;
        const onMouseDown = () => { mouseDownInMap = true; };
        const preventDragStart = (e) => {
            if (mouseDownInMap) {
                e.preventDefault();
                e.stopPropagation();
            }
        };
        container.addEventListener('mousedown', onMouseDown);
        iframeDoc.addEventListener('dragstart', preventDragStart, {capture: true});

        let cleanupForwarding = () => {};
        if (isInsideIframe) {
            // Tile images are appended to the iframe's DOM, so the browser uses
            // the iframe document's URL (a blob: URL) when determining the Referer
            // header for tile requests. Blob: URLs carry no Referer by default,
            // which causes OSM tile servers to return 403. Setting the referrer
            // policy to "origin" on the iframe document causes the browser to send
            // the page's origin (e.g. https://example.com) as the Referer instead.
            // Note: on localhost the origin is http://localhost which OSM also
            // rejects — that is an OSM policy limitation, not a code issue.
            if ( ! iframeDoc.querySelector( 'meta[name="referrer"]' ) ) {
                const referrerMeta = iframeDoc.createElement( 'meta' );
                referrerMeta.name = 'referrer';
                referrerMeta.content = 'origin';
                iframeDoc.head.appendChild( referrerMeta );
            }

            // Gutenberg's useBubbleEvents (block-editor.js) translates forwarded
            // mousemove coordinates from iframe-relative to parent-relative by adding
            // the iframe element's getBoundingClientRect() offset to clientX/Y.
            // Leaflet registers its drag mousemove handler on the parent's document
            // (Leaflet JS runs in the parent frame), so it receives these translated
            // events. But Leaflet's _startPoint was captured from the native mousedown
            // event on the container (in the iframe), so it is in iframe-relative
            // coords. The mismatch equals the iframe's top/left offset in the parent —
            // that is the "jump north" the user sees at every drag start.
            //
            // Fix: after Leaflet's _onDown has stored _startPoint (iframe coords),
            // add the iframe element's offset so _startPoint is in parent coords,
            // matching every subsequent forwarded mousemove event.
            const iframe = iframeDoc.defaultView.frameElement;
            const correctLeafletStartPoint = () => {
                if (iframe && map.dragging && map.dragging._draggable) {
                    const rect = iframe.getBoundingClientRect();
                    map.dragging._draggable._startPoint.x += rect.left;
                    map.dragging._draggable._startPoint.y += rect.top;
                }
            };
            // Leaflet registers its _onDown on the container before our listener
            // (map is initialised before this effect runs), so _startPoint is already
            // set when correctLeafletStartPoint fires.
            container.addEventListener('mousedown', correctLeafletStartPoint);

            // mouseup is not forwarded by Gutenberg's useBubbleEvents.
            // Forward it manually so Leaflet's drag _onUp handler fires.
            const forwardMouseUp = () => {
                mouseDownInMap = false;
                document.dispatchEvent(new MouseEvent('mouseup', {bubbles: true}));
            };
            const clearMouseDown = () => { mouseDownInMap = false; };
            iframeDoc.addEventListener('mouseup', forwardMouseUp);
            window.addEventListener('mouseup', clearMouseDown);

            cleanupForwarding = () => {
                container.removeEventListener('mousedown', correctLeafletStartPoint);
                iframeDoc.removeEventListener('mouseup', forwardMouseUp);
                window.removeEventListener('mouseup', clearMouseDown);
            };
        } else {
            const clearMouseDown = () => { mouseDownInMap = false; };
            document.addEventListener('mouseup', clearMouseDown);
            cleanupForwarding = () => document.removeEventListener('mouseup', clearMouseDown);
        }

        return () => {
            container.removeEventListener('mousedown', onMouseDown);
            iframeDoc.removeEventListener('dragstart', preventDragStart, {capture: true});
            cleanupForwarding();
        };
    }, [map]);
    useEffect(() => {
        if (true === showDefaultBounds) {
            // noinspection JSUnresolvedVariable
            setAttributes({
                bounds: ootbGlobal.defaultLocation,
                showDefaultBounds: false
            });
        }
        if (true === shouldUpdateBounds) {
            getBounds(props, [], map);
            setAttributes({
                shouldUpdateBounds: false
            });
        }
        if (true === shouldUpdateZoom && zoom !== map.getZoom()) {
            map.setZoom(zoom);
            setAttributes({shouldUpdateZoom: false});
        }
    }, []);
    return null;
}
