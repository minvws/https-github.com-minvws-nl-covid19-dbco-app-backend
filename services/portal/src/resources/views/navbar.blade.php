<!-- Start of navbar component -->
<div>
<b-navbar toggleable="lg" type="dark" fixed="top" class="navbar-custom">
    <b-navbar-brand href="/"><i class="icon icon--breadcrumb-back"></i>Mijn cases</b-navbar-brand>

    <b-navbar-toggle target="nav-collapse"></b-navbar-toggle>

    <b-collapse id="nav-collapse" is-nav>
        <b-navbar-nav class="ml-auto">
            @if (App::environment() != 'production')
                <b-nav-item>Omgeving: {{ ucfirst(App::environment()) }}</b-nav-item>
            @endif
            <b-nav-item href="#"><i class="icon icon--feedback"></i>Feedback</b-nav-item>
            <b-nav-item href="#"><i class="icon icon--helpdesk"></i>Helpdesk</b-nav-item>
            <b-nav-item href="#"><i class="icon icon--support"></i>Support</b-nav-item>
            <b-nav-item href="{{ route('user-profile')}}">{{ $userName }}</b-nav-item>
        </b-navbar-nav>
    </b-collapse>
</b-navbar>
</div>
<div class="mt-5">
    &nbsp;
    <!-- spacer to clear sticky topbar -->
</div>

<!-- End of navbar component -->
