<x-layout title="Privacyverklaring">

<x-app :includeHeader="false" :includeFooter="false">
    <div class="container-login">
        <div class="card-login card-consent">
            <div class="card-body">

                <h1 class="card-title mb-4">Dit moet je weten voor je begint:</h1>

                <form method="POST" action="{{ route('consent-store') }}" class="card-text">

                    @csrf

                    <ul class="list">
                        <li>BCO Portaal bevat medische en gevoelige informatie.</li>
                        <li>Alles wat je doet in het portaal wordt vastgelegd.</li>
                        <li>Jij als gebruiker gaat zorgvuldig om met alle informatie in het portaal.</li>
                    </ul>

                    <p>Wil je meer weten? Lees de <a href="/consent/privacy" target="_blank" class="">privacyverklaring</a>.</p>

                    <div class="toggle-group px-4 py-3 mb-4">
                        <div data-classification="box" data-type="checkbox" class="formulate-input-group-item formulate-input">
                            <div class="formulate-input-wrapper">
                                <div data-type="checkbox" class="formulate-input-element formulate-input-element--checkbox"><!---->
                                    <input type="checkbox" id="consent" name="consent" value="false">
                                    <label for="consent" class="formulate-input-element-decorator"></label> <!---->
                                </div>
                                <label for="consent" class="formulate-input-label formulate-input-label--after">
                                    Ik heb bovenstaande gelezen en ben klaar om BCO Portaal te gebruiken.
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" id="submit-button" class="btn btn-primary btn-block" disabled="disabled">
                            Doorgaan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app>

<script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function () {
        let checkbox = document.getElementById('consent');
        let button = document.getElementById('submit-button');

        checkbox.addEventListener('change', (event) => {
            button.disabled = !event.currentTarget.checked;
        });
    });
</script>

</x-layout>
