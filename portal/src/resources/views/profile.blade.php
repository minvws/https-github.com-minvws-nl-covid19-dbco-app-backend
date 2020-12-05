<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GGD BCO portaal - Gebruikersprofiel</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}"></script>
</head>
<body>

<div class="container-xl">
@include ('navbar')
    <div class="row  flex-nowrap  wrapper">
        <main class="col ml-5 mr-5">
            <h2 class="mt-4  mb-4  font-weight-normal d-flex align-items-end">
                <span class="font-weight-bold">{{ $user->name }}</span>
                <span class="ml-auto">
                    <form action="/logout" method="POST" autocomplete="off">
                        @csrf
                        <button class="btn btn-primary" role="button">Uitloggen</button>
                    </form>
                </span>
            </h2>
            <!-- End of page title component -->
            <p>
                Organisaties:
            <ul>
                @foreach ($user->organisations as $organisation)
                    <li>{{ $organisation->name }} ({{ $organisation->externalId }})</li>
                @endforeach
            </ul>
            </p>
            <p>
                Toegekende rollen:
                <ul>
                    @foreach ($user->roles as $role)
                        <li>{{ $roles[$role] ?? $role }}</li>
                    @endforeach
                </ul>
            </p>


        </main>
    </div>
</div>

<!-- Bootstrap core JavaScript -->
<!-- build:js -->
<!-- endbuild -->
</body>
</html>
