<div id="map" style="width: 100%; height: 400px;"></div>

<script>
    let map;
    let marker;
    let autocomplete;
    let geocoder;
    let timezoneService;

    function initMap() {
        const defaultPosition = { lat: 52.2297, lng: 21.0122 };

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

        const input = document.getElementById('address_search');
        autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['geocode'],
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            const location = place.geometry.location;

            map.setCenter(location);
            map.setZoom(12);
            marker.setPosition(location);

            setLocation(
                location.lat(),
                location.lng(),
                place.formatted_address,
                place
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

    function setLocation(lat, lng, address, place = null) {
        document.getElementById('start_lat').value = lat;
        document.getElementById('start_lng').value = lng;

        if (address !== null) {
            document.getElementById('start_address').value = address;
        }

        if (place) {
            extractCityDataFromPlace(place, lat, lng);
        }
    }

    function reverseGeocode(latLng) {
        geocoder.geocode({ location: latLng }, (results, status) => {
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

    function extractCityDataFromPlace(place, lat, lng) {
        let cityName = null;
        let countryCode = null;
        let cityPlaceId = null;

        if (place.address_components) {
            for (const component of place.address_components) {
                if (
                    component.types.includes('locality') ||
                    component.types.includes('postal_town')
                ) {
                    cityName = component.long_name;
                    cityPlaceId = component.place_id ?? place.place_id;
                }

                if (component.types.includes('country')) {
                    countryCode = component.short_name;
                }
            }
        }

        if (cityName) {
            document.getElementById('city_name').value = cityName;
        }

        if (countryCode) {
            document.getElementById('city_country_code').value = countryCode;
        }

        if (cityPlaceId) {
            document.getElementById('city_place_id').value = cityPlaceId;
        }

        // Центр города — берём текущую точку (нормально для практики)
        document.getElementById('city_lat').value = lat;
        document.getElementById('city_lng').value = lng;
    }

    window.initMap = initMap;
</script>

<script async
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.map_key') }}&libraries=places&callback=initMap&language={{ app()->getLocale() }}">
</script>

<style>
    .pac-container {
        z-index: 2000 !important;
    }
</style>