<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Login</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<!-- Start of login component -->
<div class="container-login">
    <div class="card-login">
        <img src="{{ asset('images/illustration-login.svg') }}" class="card-img-top" alt="Afbeelding digitaal contactonderzoek"/>

        <div class="card-body">
            <h1 class="card-title">Digitaal contactonderzoek</h1>
        </div>

        <div class="card-body">
            <p class="card-text">...</p>
        </div>

        <div class="card-footer">
            <a href="/auth/identityhub" class="btn  btn-primary  btn-block">Inloggen met IdentityHub</a>

            <a href="/auth/stub" class="btn  btn-primary  btn-block">Inloggen met development stub user</a>
        </div>
    </div>
</div>
<!-- End of login component -->

<!-- Bootstrap core JavaScript -->
</body>
</html>
