<div id="map" style="width: 100%; height: 400px;"></div>

<script>
    let map;
    let marker;
    let autocomplete;

    function initMap() {
        const defaultPosition = { lat: 52.2297, lng: 21.0122 }; // Warsaw

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultPosition,
            zoom: 8,
        });

        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true,
        });

        setLatLng(defaultPosition.lat, defaultPosition.lng);

        // --- Autocomplete ---
        const input = document.getElementById('address_search');
        autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();

            if (!place.geometry) {
                return;
            }

            const location = place.geometry.location;

            map.setCenter(location);
            map.setZoom(12);

            marker.setPosition(location);

            setLatLng(location.lat(), location.lng());
        });

        // Клик по карте
        map.addListener("click", (event) => {
            marker.setPosition(event.latLng);
            setLatLng(event.latLng.lat(), event.latLng.lng());
        });

        // Drag маркера
        marker.addListener("dragend", (event) => {
            setLatLng(event.latLng.lat(), event.latLng.lng());
        });

        // Фикс для Orchid modal
        setTimeout(() => {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(marker.getPosition());
        }, 300);
    }

    function setLatLng(lat, lng) {
        document.getElementById('start_lat').value = lat;
        document.getElementById('start_lng').value = lng;
    }

    window.initMap = initMap;
</script>

<script async
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.map_key') }}&libraries=places&callback=initMap">
</script>

<style>
    .pac-container {
        z-index: 2000 !important;
    }
</style>