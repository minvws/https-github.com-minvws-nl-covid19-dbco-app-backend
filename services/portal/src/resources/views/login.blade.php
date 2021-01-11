<x-layout>
<x-slot name="title">
    Login
</x-slot>

<!-- Start of login component -->
<div class="container-login">
    <div class="card-login">
        <img src="{{ asset('images/illustration-login.svg') }}" class="card-img-top" alt="Afbeelding digitaal contactonderzoek"/>

        <div class="card-body">
            <h1 class="card-title">BCO Portaal</h1>
        </div>

        <div class="card-body">
            <p class="card-text">Digitaal contactonderzoek</p>
        </div>

        <div class="card-footer">
            <div class="row">
            <div class="col">

            <a href="/auth/identityhub" class="btn  btn-primary  btn-block">Inloggen</a>
            </div></div>
            @if ($allowDemoLogin)
            <div class="row mt-4">
                <div class="col">
                    <a href="/auth/stub?role=user" class="btn  btn-primary  btn-block">Demo login: Gebruiker</a>
                </div>
                <div class="col">
                    <a href="/auth/stub?role=admin" class="btn  btn-primary  btn-block">Demo login: Beheerder</a>
                </div>
                <div class="col">
                    <a href="/auth/stub?role=planner" class="btn  btn-primary  btn-block">Demo login: Verdeler</a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- End of login component -->

<!-- Bootstrap core JavaScript -->
</x-layout>
