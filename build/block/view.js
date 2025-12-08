/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/common/createMapboxStyleUrl.js":
/*!********************************************!*\
  !*** ./src/common/createMapboxStyleUrl.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ createMapboxStyleUrl)
/* harmony export */ });
/**
 * Converts Mapbox style URL into a format that Leaflet can use for requesting tiles.
 * @param {string} styleUrl - The Mapbox style URL.
 * @param {string} accessToken - The Mapbox access token.
 * @returns {string} The Leaflet URL.
 */
function createMapboxStyleUrl(styleUrl, accessToken) {
  if (!styleUrl || !accessToken || typeof styleUrl !== 'string' || typeof accessToken !== 'string' || !styleUrl.startsWith('mapbox://')) {
    return '';
  }
  // Parse username and style ID from Mapbox URL
  const mapboxUrlParts = styleUrl.split('/');
  const username = mapboxUrlParts[3];
  const styleId = mapboxUrlParts[4];
  if (!username || !styleId || typeof username !== 'string' || typeof styleId !== 'string') {
    return '';
  }

  // Build Leaflet URL
  return `https://api.mapbox.com/styles/v1/${username}/${styleId}/tiles/{z}/{x}/{y}?access_token=${accessToken}`;
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!***************************!*\
  !*** ./src/block/view.js ***!
  \***************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _common_createMapboxStyleUrl_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../common/createMapboxStyleUrl.js */ "./src/common/createMapboxStyleUrl.js");
// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

(function () {
  'use strict';

  const providers = ootb.providers;
  const options = ootb.options;
  const gestureHandlingOptions = ootb.gestureHandlingOptions;
  const maps = document.querySelectorAll('.ootb-openstreetmap--map');
  maps.forEach(renderMap);
  function renderMap(osmap) {
    const provider = osmap.getAttribute('data-provider') || 'openstreetmap';
    const escapedMarkers = osmap.getAttribute('data-markers');
    const escapedDefaultIcon = osmap.getAttribute('data-marker');
    const zoom = osmap.getAttribute('data-zoom');
    const minZoom = osmap.getAttribute('data-minzoom');
    const maxZoom = osmap.getAttribute('data-maxzoom');
    const dragging = osmap.getAttribute('data-dragging');
    const touchZoom = osmap.getAttribute('data-touchzoom');
    const doubleClickZoom = osmap.getAttribute('data-doubleclickzoom');
    const scrollWheelZoom = osmap.getAttribute('data-scrollwheelzoom');
    const fullscreen = osmap.getAttribute('data-fullscreen');
    const defaultIcon = JSON.parse(decodeURIComponent(escapedDefaultIcon));
    const locations = JSON.parse(decodeURIComponent(escapedMarkers));
    const mapType = osmap.getAttribute('data-maptype');
    const showMarkers = osmap.getAttribute('data-showmarkers');
    const escapedShapeStyle = osmap.getAttribute('data-shapestyle');
    const shapeStyle = JSON.parse(decodeURIComponent(escapedShapeStyle));
    const shapeText = osmap.getAttribute('data-shapetext');
    const mapboxstyleAttr = osmap.getAttribute('data-mapboxstyle');
    let mapboxstyle = '';
    if (mapboxstyleAttr) {
      mapboxstyle = mapboxstyleAttr;
    } else {
      const mapboxstyleGlobal = (0,_common_createMapboxStyleUrl_js__WEBPACK_IMPORTED_MODULE_0__["default"])(options.global_mapbox_style_url, options.api_mapbox);
      if (mapboxstyleGlobal) {
        mapboxstyle = encodeURIComponent(JSON.stringify(mapboxstyleGlobal));
      }
    }
    let apiKey = '';
    if ('mapbox' === provider) {
      apiKey = options.api_mapbox;
    }
    let providerUrl = providers[provider].url;
    if ('mapbox' === provider && mapboxstyle) {
      providerUrl = JSON.parse(decodeURIComponent(mapboxstyle));
    } else {
      providerUrl += apiKey;
    }
    const mapOptions = {
      minZoom: parseInt(minZoom),
      maxZoom: parseInt(maxZoom)
    };
    if (options.prevent_default_gestures) {
      mapOptions.gestureHandling = true;
      if (gestureHandlingOptions && Object.keys(gestureHandlingOptions).length > 0) {
        mapOptions.gestureHandlingOptions = gestureHandlingOptions;
      }
    }
    const map = initializeMapView(osmap, mapOptions, zoom, locations, escapedDefaultIcon);

    // Set the rest of the map options
    if ('false' === dragging) {
      map.dragging.disable();
    }
    if ('false' === touchZoom) {
      map.touchZoom.disable();
    }
    if ('false' === doubleClickZoom) {
      map.doubleClickZoom.disable();
    }
    if ('false' === scrollWheelZoom) {
      map.scrollWheelZoom.disable();
    }
    if ('true' === fullscreen && L.Control.FullScreen) {
      map.addControl(new L.Control.Fullscreen());
    }
    L.tileLayer(providerUrl, {
      attribution: providers[provider].attribution
    }).addTo(map);
    if (!locations || !locations.length) return; // If there are no locations, don't go any further

    if ('polygon' === mapType) {
      const polygon = L.polygon(locations, shapeStyle).addTo(map);
      map.fitBounds(polygon.getBounds());
      if (shapeText.length) {
        polygon.bindPopup(shapeText);
      }
    } else if ('polyline' === mapType) {
      const polyline = L.polyline(locations, shapeStyle).addTo(map);
      map.fitBounds(polyline.getBounds());
      if (shapeText.length) {
        polyline.bindPopup(shapeText);
      }
    }
    if ('false' !== showMarkers) {
      // Render the locations
      locations.forEach(renderLocation);
    }

    // Render a location's marker
    function renderLocation(location) {
      const markerIcon = structuredClone(defaultIcon);
      if (location.icon) {
        markerIcon.iconUrl = location.icon.url;
      }
      let marker = L.marker([location.lat, location.lng], {
        icon: L.icon(markerIcon)
      });
      if (location.text) {
        marker.bindPopup(location.text);
      }
      marker.addTo(map);
    }
  }
  function initializeMapView(osmap, mapOptions, zoom, locations, defaultIconString) {
    const map = L.map(osmap, mapOptions);
    const boundsCheck = JSON.parse(osmap.getAttribute('data-bounds'));
    if (boundsCheck[0] !== null && boundsCheck[1] !== null) {
      map.setView(boundsCheck, parseInt(zoom));
    } else {
      let markers = [];
      locations.forEach(location => {
        const defaultIcon = JSON.parse(decodeURIComponent(defaultIconString));
        const markerIcon = structuredClone(defaultIcon);
        if (location.icon) {
          markerIcon.iconUrl = location.icon.url;
        }
        let marker = L.marker([location.lat, location.lng], {
          icon: L.icon(markerIcon)
        });
        if (location.text) {
          marker.bindPopup(location.text);
        }
        markers.push(marker);
        marker.addTo(map);
      });

      // Use markers to calculate bounds.
      if (markers.length) {
        // Create a new feature group.
        let group = new L.featureGroup(markers);
        // Adjust the map to show all markers.
        map.fitBounds(group.getBounds());
      }
    }
    return map;
  }
})();
})();

/******/ })()
;
//# sourceMappingURL=view.js.map