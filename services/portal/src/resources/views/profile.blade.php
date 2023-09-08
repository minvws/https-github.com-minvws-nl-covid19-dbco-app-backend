<x-layout title="Profiel">

<x-app>
    <div class="container-xl">
        <div class="row flex-nowrap wrapper">
            <main class="col m-5 pt-5">
                <h2 class="mt-5 mb-4 font-weight-normal d-flex align-items-end">
                    <span class="font-weight-bold">{{ $user->name }}</span>
                </h2>
                <!-- End of page title component -->
                <p>
                    Organisaties:
                <ul class="list">
                    @foreach ($user->organisations as $organisation)
                        <li>{{ $organisation->name }} ({{ $organisation->externalId }})</li>
                    @endforeach
                </ul>
                </p>
                <p>
                    Toegekende rollen:
                    <ul class="list">
                        @foreach ($user->getRolesArray() as $role)
                            <li>{{ $roles[$role] ?? $role }}</li>
                        @endforeach
                    </ul>
                </p>
                <p>
                    <a href="/consent/privacy" target="_blank">Privacyverklaring</a>
                </p>
            </main>
        </div>
        <div class="row">
            <div class="col pt-2 ml-5">
                <dbco-version></dbco-version>
            </div>
        </div>
    </div>
</x-app>

</x-layout>
