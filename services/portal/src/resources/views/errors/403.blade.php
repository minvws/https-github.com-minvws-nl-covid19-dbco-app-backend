<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Geen toegang</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<div class="container-xl">
    @include('navbar', ['root' => true])
    <div class="row">
        <div class="col ml-5 mr-5">
            <!-- Start of page title component -->
            <h2 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                <span class="font-weight-bold">Geen toegang</span>
                <!-- End of page title component -->
            </h2>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane  fade show  active" id="nav-own-cases" role="tabpanel" aria-labelledby="nav-own-cases-tab">
                    <div class="bg-white text-center pt-5 pb-4">
                        <p>
                            {{ $exception->getMessage() }}
                        </p>
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
            <!-- End of tabs component -->
        </div>
    </div>
</div>
</body>
</html>
