<div class="form-label">
    {{$value['title']}}
</div>
<a href="{{ $value['href'] }}" target="{{ $value['target'] ?? '' }}"
   title="{{ $value['title'] ?? '' }}"
>{{ $value['text'] }}</a>
<style>
    a {
        color: #000;
    }

    a:hover {
        color: #002770;
    }

    i:hover {
        color: #26d500;
    }
</style>