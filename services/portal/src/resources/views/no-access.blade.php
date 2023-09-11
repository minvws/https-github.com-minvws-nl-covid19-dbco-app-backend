<x-layout title="Account configuratie fout">

<x-app>
    <div class="container-xl">
        <div class="row flex-nowrap wrapper">
            <main class="col ml-5 mr-5 pt-5 mt-5">
                <h2 class="mt-4 mb-4 font-weight-normal">
                    <span class="font-weight-bold">Je account is niet correct geconfigureerd</span>
                </h2>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
                        <div class="bg-white pt-5 pb-4 px-5">
                            <p class="mb-4">
                                Je kan niet worden ingelogd vanwege een configuratie-fout in je account.
                            </p>

                            <p>
                                Mogelijke oorzaken:
                            </p>
                            <ul class="list mb-4">
                                <li>Je account is gekoppeld aan een landelijke organisatie maar je hebt een lokale rol.</li>
                                <li>Je account is gekoppeld aan een regionale organisatie maar je hebt een landelijke rol.</li>
                                <li>Je account heeft geen rollen.</li>
                            </ul>

                            <p>
                                Neem contact op met je lokale beheerder om uit te zoeken of je de juiste rollen hebt.<br />
                            </p>
                            <p class="mb-4">
                                Na het toekennen van de juiste rol moet je opnieuw inloggen om het opnieuw te proberen.
                            </p>
                            <form method="POST" action="{{ route('user-logout') }}" class="card-text">
                                @csrf
                                <button type="submit" id="submit-button" class="btn btn-primary btn-block" style="width: min-content;">
                                    Uitloggen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</x-app>

</x-layout>
