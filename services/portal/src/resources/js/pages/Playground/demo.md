# Demo dynamische formulieren / JSON Forms

### Views

Er zijn momenteel 2 "views" op de playground. Een "_admin_" view waar je nieuwe diseases kunt aanmaken. En een "_edit dossier_" view. Hier wordt de layout van een `edit-case` pagina nagebootst. Hierin wordt een formulier ingeladen van een bestaande dossier of een test formulier.

Standaard krijg je de "_admin_" view te zien op `/playground`. Als je een `dossierID` of een `testFormId` mee geeft als url parameter krijg je de "_edit dossier_" view te zien. E.g. `/playground?dossierId=5` of `/playground?testFormId=demo-form`.

### Het aanmaken van een dossier

1. Een disease wordt automatisch geselecteerd als je er net een hebt aangemaakt, zo niet moet je hem selecteren in het _Disease_ gedeelte door op _Select_ te klikken.
2. Ga naar het _Dossier aanmaken voor {disease}: version x_
3. Maak je een dossier aan voor een test formulier? Selecteer dan dit test formulier in de dropdown, zodat er test data ingevuld wordt.
4. Klik op "Create dossier" _Als dit goed gaat krijg je de `id` te zien van het nieuwe dossier naast de knop `Create dossier`._

## Demo

Voor de demo is er het volgende scenario bedacht:

### Voorbereiding:

1. Open de admin view door naar [`/playground`](http://localhost:8084/playground) te gaan.
2. Maak een nieuwe disease aan met de `demo-form` configuratie (dit kan je selecteren in de dropdown).
3. Maak een nieuw dossier aan voor deze disease, zie instructies hierboven.
4. Ga naar de edit view van het dossier.

-   Op de link te klikken naast `Create dossier` wanneer je er net een hebt aagemaakt.
-   Dit doe je door het dossier id in het _Dossier_ gedeelte in te vullen en te klikken op "Open dossier"
-   Of handmatig naar [`/playground?dossierId={dossierId}`](http://localhost:8084/playground?dossierId=XXX) te gaan.

### Tijdens de demo:

Nu kan je in de edit view het volgende laten zien.

-   het dossier kan aangepast worden en als je refreshed zie je dat de informatie is opgeslagen.
-   Het bevat functionaliteit om delen te verbergen of te laten zien afhanklijk van de data (ja / nee / onbekend)
-   Het bevat meldingen die in de schemas definieerd zijn.
-   Het bevat meerdere type velden
-   Er kan doorgelinked worden naar een volledig formulier mbt contact ???

Na deze eerste sessie kan je de disease aanpassen:

1. Ga naar de [admin view](http://localhost:8084/playground)
2. Selecteer je eerder aangemaakte disease
3. Bij het _disease_ gedeelte selecteer je een nieuw formulier `demo-form-update`
4. Klik op _Wijzig_
5. Maak een nieuw _dossier_ aan voor dezelfde disease, zie instructies hierboven. Selecteer hierbij voor de juiste test data `demo-form-2`
6. Ga naar de edit view van het dossier.

Nu kan je het volgende laten zien.

-   het dossier bevat nieuwe velden.
-   het dossier kan nog steeds aangepast worden en als je refreshed zie je dat de informatie is opgeslagen.
-   Als je terug gaat naar het oude dossier, is dit onveranderd en werkt nog steeds.

### Tot slot - een nieuwe disease

1. Maak een nieuwe disease aan met het formulier `new-disease`
2. Maar een nieuw dossier aan voor dit formulier.
3. Ga naar de edit view van dit dossier

Nu kan je het volgende laten zien.

-   Er is een dossier voor een nieuwe disease aangemaakt!
-   Het formulier ziet er compleet anders uit maar werkt nog volledig.
