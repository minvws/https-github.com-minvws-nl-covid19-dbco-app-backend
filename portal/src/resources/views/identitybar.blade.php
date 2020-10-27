
    <div class="collapse  navbar-collapse" id="navbarToggler">
        <ul class="navbar-nav  ml-auto  mt-2  mt-lg-0">
            @if (App::environment() != 'production')
            <li class="nav-item">
                <a class="nav-link" href="#">Omgeving: {{ App::environment() }}</a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link" href="#">Ingelogd als {{ $userName }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">eLearning</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Helpdesk</a>
            </li>
        </ul>
    </div>
