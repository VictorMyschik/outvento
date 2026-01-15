<img id="qr" src="" alt="QR code" width="120" height="120"/>

<script>
    const value = '{{ $value }}';
    const qr = document.getElementById('qr');

    qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
        + encodeURIComponent(value);
</script>
