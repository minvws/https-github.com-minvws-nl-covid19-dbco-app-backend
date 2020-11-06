<!-- Start of navbar component -->
<div class="row">
    <nav class="navbar  navbar-expand-lg  navbar-light  bg-white  w-100">
        @if (!($root ?? false))
        <a href="{{ $returnPath ?? '/' }}" class="btn  btn-light  rounded-pill">
            <i class="icon  icon--arrow-left  icon--m0"></i>
        </a>
        @endif

        <button class="navbar-toggler  ml-auto  bg-white"
                type="button"
                data-toggle="collapse"
                data-target="#navbarToggler"
                aria-controls="navbarToggler"
                aria-expanded="false" aria-label="Navigatie tonen">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse  navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav  ml-auto  mt-2  mt-lg-0">
                @if (App::environment() != 'production')
                <li class="nav-item">
                    <a class="nav-link" href="#">Omgeving: {{ App::environment() }}</a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="/profile">Ingelogd als {{ $userName }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">eLearning</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Helpdesk</a>
                </li>
            </ul>
        </div>
    </nav>
</div>
<!-- End of navbar component -->
