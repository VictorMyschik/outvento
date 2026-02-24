<div id="map" style="width: 100%; height: 400px;"></div>

<script>
    let map;
    let marker;
    let autocomplete;

    function initMap() {
        const defaultPosition = { lat: 52.2297, lng: 21.0122 };

        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultPosition,
            zoom: 8,
        });

        marker = new google.maps.Marker({
            position: defaultPosition,
            map: map,
            draggable: true,
        });

        setLocation(defaultPosition.lat, defaultPosition.lng, null);

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

            setLocation(
                location.lat(),
                location.lng(),
                place.formatted_address
            );
        });

        map.addListener("click", (event) => {
            marker.setPosition(event.latLng);
            reverseGeocode(event.latLng);
        });

        marker.addListener("dragend", (event) => {
            reverseGeocode(event.latLng);
        });

        setTimeout(() => {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(marker.getPosition());
        }, 300);
    }

    function setLocation(lat, lng, address) {
        document.getElementById('start_lat').value = lat;
        document.getElementById('start_lng').value = lng;

        if (address !== null) {
            document.getElementById('start_address').value = address;
        }
    }

    function reverseGeocode(latLng) {
        const geocoder = new google.maps.Geocoder();

        geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === "OK" && results[0]) {
                setLocation(
                    latLng.lat(),
                    latLng.lng(),
                    results[0].formatted_address
                );
            }
        });
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