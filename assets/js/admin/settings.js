(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', (event) => {
        function toggleGeoPostTypes() {
            const checkbox = document.querySelector('#geodata');
            const geoPostTypesRow = document.querySelector('.ootb--geo_post_types');

            geoPostTypesRow.style.display = checkbox.checked ? 'table-row' : 'none';
        }

        // On page load
        toggleGeoPostTypes();

        // When the checkbox changes
        document.querySelector('#geodata').addEventListener('change', toggleGeoPostTypes);
    });
})();