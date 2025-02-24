<li class="nav-item dropdown">
    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
       data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
        <span class="">{{ mb_strtoupper(app()->getLocale()) }}</span> <span class="caret"></span>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
        @foreach(\App\Models\System\Language::all() as $item)
            @if($item->getCode() == mb_strtoupper(app()->getLocale()))
                @continue
            @endif
            <a href="{{ url('/locale/'.mb_strtolower($item->getCode())) }}" class="dropdown-item">
                <i class="nav-item mr-color-green-dark"></i> {{ $item->getCode() . ' ' . $item->getName() }}</a>
        @endforeach
    </div>
</li>
