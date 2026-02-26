<div id="map" style="width: 100%; height: 400px;"></div>

<script>
    let map;
    let marker;
    let autocomplete;
    let geocoder;
    let timezoneService;

    function initMap() {
        const defaultPosition = {lat: {{ (float)$value['lat'] }}, lng: {{ (float)$value['lng'] }}};

        geocoder = new google.maps.Geocoder();

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

    function setLocation(lat, lng, address, place = null) {
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;

        if (address !== null) {
            document.getElementById('address').value = address;
        }

        if (place) {
            extractCityDataFromPlace(place, lat, lng);
        }
    }

    function reverseGeocode(latLng) {
        geocoder.geocode({location: latLng}, (results, status) => {
            if (status === "OK" && results[0]) {
                setLocation(
                    latLng.lat(),
                    latLng.lng(),
                    results[0].formatted_address,
                    results[0]
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