<div class="mb-3">
    <label class="form-label">City</label>
    <input
            id="city-input"
            type="text"
            class="form-control"
            placeholder="Start typing city..."
    >

    <input type="hidden" name="countryCode" id="countryCode">
    <input type="hidden" name="placeId" id="placeId">
    <input type="hidden" name="lat" id="lat">
    <input type="hidden" name="lng" id="lng">
    <input type="hidden" name="cityName" id="cityName">
</div>

<script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.map_key') }}&libraries=places&language={{ app()->getLocale() }}">
</script>

<script>
    function initCityAutocomplete() {
        const input = document.getElementById('city-input');

        if (!input || input.dataset.autocompleteInitialized) {
            return;
        }

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['(cities)'],
        });

        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();

            if (!place.geometry || !place.address_components) {
                return;
            }

            let countryCode = null;

            for (const component of place.address_components) {
                if (component.types.includes('country')) {
                    countryCode = component.short_name; // RU, PL, DE
                    break;
                }
            }

            document.getElementById('placeId').value = place.place_id;
            document.getElementById('lat').value = place.geometry.location.lat();
            document.getElementById('lng').value = place.geometry.location.lng();
            document.getElementById('countryCode').value = countryCode;
            document.getElementById('cityName').value = place.name;
        });

        input.dataset.autocompleteInitialized = '1';
    }

    // 🔥 ключевой момент
    document.addEventListener('shown.bs.modal', function () {
        initCityAutocomplete();
    });
</script>

<style>
    .pac-container {
        z-index: 2000 !important;
    }
</style>
