<x-layout title="Geen toegang">

<x-app>
    <div class="container-xl">
        <div class="row  flex-nowrap  wrapper">
            <main class="col ml-5 mr-5 mb-5 pt-5">
                <h2 class="mt-5 mb-4 pt-5 font-weight-normal d-flex align-items-end">
                    <span class="font-weight-bold">Geen toegang</span>
                    <!-- End of page title component -->
                </h2>

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
                        <div class="bg-white text-center pt-5 pb-4">
                            <p>
                                Bekijk <a href="/profile">je profiel pagina</a> en controleer of de juiste rol is toegekend aan je account.
                                <br/>Indien dit niet het geval is, neem dan contact op met je lokale beheerder.
                            </p>
                            <p>
                                Na het toekennen van de juiste rol moet je <a href="/login">opnieuw inloggen</a> om het opnieuw te proberen.
                            </p>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
</x-app>

</x-layout>
