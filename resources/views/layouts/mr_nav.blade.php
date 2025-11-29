<nav class="navbar navbar-expand-md shadow-sm shadow fixed-top bg-white"
     style="height: 90px; font-size: 1.1rem; z-index: 1080 !important;">
    <div class="container">
        <a class="navbar-brand text-italic mr-nav-link-color" href="{{ url('/') }}">
            <span class="mr-nav-link-color" style="font-size: 1.5rem;">My Travel</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto">

            </ul>
            <ul class="navbar-nav me-auto">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('faq.page') }}">
                            <span class="mr-nav-link-color">FAQ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <span class="mr-nav-link-color">{{ __('mr-t.login') }}</span>
                        </a>
                    </li>
                    <li class="nav-item mr-5">
                        <a class="nav-link" href="{{ route('register') }}">
                            <span class="mr-nav-link-color">{{ __('mr-t.register') }}</span>
                        </a>
                    </li>
                @else
                    <new_travel :lang='@json($lang)'></new_travel>

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link mr-nav-link-color dropdown-toggle font-weight-bolder"
                           href="#" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                           v-pre>{{__('mr-t.my_travels')}}<span class="caret"></span>
                        </a>

                        <nav_bar></nav_bar>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('faq.page') }}">
                            <span class="mr-nav-link-color">FAQ</span>
                        </a>
                    </li>

                    <li class="nav-item mr-5">
                        <a class="nav-link" href="/account">
                            <span class="mr-nav-link-color">{{__('mr-t.account')}}</span>
                        </a>
                    </li>

                    @if(auth()->user()->isSuperAdmin())
                        <li class="nav-item mr-5">
                            <a class="nav-link" href="/admin"><span class="mr-nav-link-color">Admin</span></a>
                        </li>
                    @endif

                @endguest
            </ul>
            <ul class="navbar-nav ml-auto">
                @include('layouts.language')
                @include('layouts.Elements.logout')
            </ul>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

    </div>
</nav>
