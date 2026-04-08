<div class="gallery">
    @foreach($value as $image)
        <div class="gallery-item">
            <a class="gallery-item--link" href="{{ $image->url }}" target="_blank">
                <img src="{{ $image->url }}" alt="{{ $image->id }}" loading="lazy">
            </a>

            <div class="gallery-item--desc">
                {{ $image->description }}
            </div>

            <div class="gallery-item--footer">
                {{ $image->btn }}
            </div>
        </div>
    @endforeach
</div>

<style>
    .gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
        padding: 1rem 0;
    }

    .gallery-item {
        display: flex;
        flex-direction: column;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .gallery-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .gallery-item--link {
        display: block;
        width: 100%;
        aspect-ratio: 4 / 3; /* держит пропорцию */
        overflow: hidden;
    }

    .gallery-item--link img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .gallery-item--desc {
        padding: 8px 10px;
        font-size: 14px;
        flex-grow: 1;
    }

    .gallery-item--footer {
        padding: 8px 10px;
        border-top: 1px solid #eee;
    }

    .gallery-item {
        position: relative;
    }

    .gallery-item--footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .gallery-item:hover .gallery-item--footer {
        opacity: 1;
    }

    .gallery-item--footer {
        background: #fff;
        border-top: 1px solid #eee;
    }

    .gallery-item--footer a {
        color: #000;
        font-weight: 500;
    }
</style>