## Ticket

https://ggdcontact.atlassian.net/browse/<VOEG HIER TICKET ID TOE>

<Evt een korte toelichting van je aanpak>

## Algemeen
- [ ] Link naar deze PR toegevoegd aan ticket in een comment
- [ ] Acceptatiecriteria zijn behaald
- [ ] Acceptatiecriteria hebben geautomatiseerde tests

## Code kwaliteit
- [ ] Geen code die niet af is (geen todo’s); een ticket maken en nummer toevoegen als comment indien dit vanwege legacy code echt noodzakelijk is
- [ ] Testwaardes verwijzen naar implementatie constanten of worden via faker gegenereerd

### Backend
- [ ] Zijn er migraties toegevoegd? (info voor QA)
- [ ] Alle nieuwe acties die Guida moet uitvoeren naast het deployen van de release (zetten van vars, commando's uitvoeren) zijn gedocumenteerd in *HOSTING_CHANGELOG.md*
- [ ] Nieuwe Prometheus metrics zijn gedocumenteerd in de [*Meldingen metrics*](https://ggdcontact.atlassian.net/wiki/spaces/GGDDBCO/pages/25657399/Prometheus+Metrics) sheet.
  - [ ] Nieuwe Prometheus metrics zijn ingeregeld in grafana, of er is hier een issue voor aangemaakt.

## Security

### Input Validatie
- [ ] Binnenkomende data wordt gevalideerd op: type, inhoud en lengte van input tegen een lijst van toegestane letters, cijfers en leestekens.
- [ ] Waardes in headers van requests worden gecheckt en gevalideerd.
- [ ] Alle data wordt gevalideerd vóór verwerking (inclusief URLs, HTTP headers, embedded code etc.).

### Output Encoding
- [ ] Output encoding is gebruikt (relevant bij velden die HTML kunnen bevatten) om ongewenste code uitvoering te voorkomen.

### Authenticatie
- [ ] Authenticatie vereist voor alle controllers.

### Toegangscontrole
- [ ] Controllers en andere code passen toegangscontrole toe op basis van privilege niveau.
- [ ] Alleen geautoriseerde gebruikers hebben toegang tot benodigde resources.
- [ ] Audit middleware is toegepast op nieuwe routes.
- [ ] Rate limiting is toegepast waar logisch / mogelijk.

### Omgaan met Fouten en Logging
- [ ] Geen gevoelige informatie in log calls.

### Data Bescherming
- [ ] Het 'Least privilege' principe is toegepast.
- [ ] Gevoelige data uitgesloten van GET requests in HTTP.
- [ ] Geen autocomplete (van de browser) functionaliteit voor het invullen van formulieren.
