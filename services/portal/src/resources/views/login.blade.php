<x-layout title="Login">

<x-app :includeHeader="false" :includeFooter="false">
    <div class="container-login">
        <div class="card-login">
            <img src="{{ asset('static/illustration-login.svg') }}" class="card-img-top" alt="Afbeelding digitaal contactonderzoek"/>

            <div class="card-body">
                <h1 class="card-title">BCO Portaal</h1>
            </div>

            <div class="card-body {{ $environmentName === 'training' ? 'mb-4' : '' }}">
                <p class="card-text">Digitaal contactonderzoek</p>
            </div>

            <div class="card-footer">
                @if ($environmentName !== 'training')
                    <a href="/auth/identityhub" class="btn btn-primary btn-block mb-3">Inloggen</a>
                @endif
                
                @if (count($demoUsersDefault) > 0)
                    @foreach ($demoUsersDefault as $userGroup)
                        <div class="button-group">
                            @foreach ($userGroup as $i => $user)
                                <a href="/auth/stub?uuid={{ $user['uuid'] }}" class="btn btn-primary btn-block mt-0 align-middle">{{ $user['label'] }}</a>
                            @endforeach
                        </div>
                    @endforeach
                @endif

                @if (count($demoUsersOtherRoles) > 0)
                    <collapse v-cloak class="mb-4 text-center" label-closed="Toon alle demorollen" label-open="Toon minder demorollen">
                        @foreach ($demoUsersOtherRoles as $userGroup)
                            <div class="button-group">
                                @foreach ($userGroup as $i => $user)
                                    <a href="/auth/stub?uuid={{ $user['uuid'] }}" class="btn btn-primary btn-block mt-0 align-middle">{{ $user['label'] }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </collapse>
                @endif

                @if (count($demoUsersDefault) > 0)
                    <form class="col" method="POST" action="{{ route('consent-reset') }}">
                        @csrf
                        <button type="submit" class="btn btn-link btn-block">Reset consent</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col pt-2 pl-5">
                <dbco-version></dbco-version>
            </div>
        </div>
    </div>
</x-app>

</x-layout>
