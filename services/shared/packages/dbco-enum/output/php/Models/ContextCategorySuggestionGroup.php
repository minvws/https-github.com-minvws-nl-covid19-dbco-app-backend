<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Context category suggestion groups.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategorySuggestionGroup.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContextCategorySuggestionGroup overig() overig() Overig
 * @method static ContextCategorySuggestionGroup sport() sport() Sport
 * @method static ContextCategorySuggestionGroup onderwijs() onderwijs() Onderwijs
 * @method static ContextCategorySuggestionGroup kinderopvang() kinderopvang() Kinderopvang
 * @method static ContextCategorySuggestionGroup religieuzeBijeenkomst() religieuzeBijeenkomst() Religieuze bijeenkomst
 * @method static ContextCategorySuggestionGroup koor() koor() Koor
 * @method static ContextCategorySuggestionGroup studentenverening() studentenverening() Studentenverening
 * @method static ContextCategorySuggestionGroup verenigingOverige() verenigingOverige() Overige verenigingen
 * @method static ContextCategorySuggestionGroup overigeAndereWerkplek() overigeAndereWerkplek() Overige andere werkplek
 * @method static ContextCategorySuggestionGroup vleesverwerkingSlachthuis() vleesverwerkingSlachthuis() Vleesverwerking / Slachthuis
 * @method static ContextCategorySuggestionGroup landEnTuinbouw() landEnTuinbouw() Land- en tuinbouw
 * @method static ContextCategorySuggestionGroup bouw() bouw() Bouw
 * @method static ContextCategorySuggestionGroup fabriek() fabriek() Fabriek
 * @method static ContextCategorySuggestionGroup kantoorOverigeBranche() kantoorOverigeBranche() Kantoor overige branche
 * @method static ContextCategorySuggestionGroup horeca() horeca() Horeca
 * @method static ContextCategorySuggestionGroup horecaOverig() horecaOverig() Horeca overig
 * @method static ContextCategorySuggestionGroup buitenland() buitenland() Buitenland reis
 * @method static ContextCategorySuggestionGroup zeeTransport() zeeTransport() Schip / Zee- en binnenvaart / Haven
 * @method static ContextCategorySuggestionGroup vliegTransport() vliegTransport() Vliegreis / Luchthaven
 * @method static ContextCategorySuggestionGroup reizenVervoerOverig() reizenVervoerOverig() Overig transport
 * @method static ContextCategorySuggestionGroup zorg() zorg() Zorg
 * @method static ContextCategorySuggestionGroup zorgOverig() zorgOverig() Zorg overig
 * @method static ContextCategorySuggestionGroup evenement() evenement() Evenement
 * @method static ContextCategorySuggestionGroup maatschappelijkeOpvang() maatschappelijkeOpvang() Maatschappelijke opvang
 * @method static ContextCategorySuggestionGroup thuissituatieBezoek() thuissituatieBezoek() Thuissituatie / bezoek
 * @method static ContextCategorySuggestionGroup accommodatie() accommodatie() Accommodatie
 * @method static ContextCategorySuggestionGroup retail() retail() Retail
 * @method static ContextCategorySuggestionGroup zwembad() zwembad() Zwembad

 * @property-read string $value
 * @property-read array $suggestions Suggestions
*/
final class ContextCategorySuggestionGroup extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContextCategorySuggestionGroup',
           'tsConst' => 'contextCategorySuggestionGroup',
           'description' => 'Context category suggestion groups.',
           'properties' =>
          (object) array(
             'suggestions' =>
            (object) array(
               'type' => 'array',
               'description' => 'Suggestions',
               'phpType' => 'array',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'overig',
               'label' => 'Overig',
               'suggestions' =>
              array (
                0 => 'Type bijeenkomst? Duur?',
                1 => 'Was het een georganiseerde bijeenkomst of een niet-georganiseerde bijeenkomst?',
                2 => 'Nauw/lichamelijk contact (bijv. omhelzing)?',
                3 => 'Zijn er mensen uit één sociale omgeving (werk/klas/opleiding) of uit meerdere sociale omgevingen aanwezig? Hoe groot was de groep?',
                4 => 'Was het continu dezelfde groep, of was er een constante wisseling van de groep mensen?',
                5 => 'Zijn er onregelmatigheden geweest? Was er sprake van fysiek contact?',
              ),
               'name' => 'overig',
            ),
            1 =>
            (object) array(
               'value' => 'sport',
               'label' => 'Sport',
               'suggestions' =>
              array (
                0 => 'Beschrijving van de locatie/sportruimte?',
                1 => 'Welke sport(les) is er beoefend/gegeven?',
                2 => 'Betreft het een contactsport?',
                3 => 'Betreft het een groepssport? Zo ja, wat was de groepsgrootte?',
                4 => 'Gaat het om binnen- of buitensport?',
                5 => 'Is er gebruikgemaakt van kleedkamers? Zo ja, waren dit groepskleedkamers of individuele kleedkamers?',
              ),
               'name' => 'sport',
            ),
            2 =>
            (object) array(
               'value' => 'onderwijs',
               'label' => 'Onderwijs',
               'suggestions' =>
              array (
                0 => 'Welke onderwijsvorm?',
                1 => 'Welke locatie en koepelorganisatie?',
                2 => 'Bij PO of VO: welke klas(sen), groep(en) en/of leerjaar?',
                3 => 'Bij HBO of universiteit: welke afdeling, opleiding en/of leerjaar?',
                4 => 'Meerdere locaties? Zo ja, welke?',
                5 => 'Bij medewerker: wat is de functie?',
                6 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                7 => 'Bij medewerker: met welke personen was er nauw contact?',
                8 => 'Bij student/leerling: bij welke docenten en/of medestudenten/leerlingen in de buurt geweest tijdens besmettelijke periode, noteer per dag (namen)?',
                9 => 'Bij student/leerling: welke lessen gevolgd tijdens besmettelijke periode per dag? (denk ook aan: plusgroepen, extra lessen, huiswerkklas)',
                10 => 'Zit klas of groep al (deels) in quarantaine en zo ja, vanaf wanneer?',
                11 => 'Aantal leerlingen/studenten in klas en in bubbel?',
                12 => 'Positieve uitslag bekend bij school?',
                13 => 'Onrust over COVID-19 beleid in de school?',
                14 => 'Is er gebruik gemaakt van gezamenlijk leerlingenvervoer? (NSO/SO)',
                15 => 'Mag naam index ook naar klas- of groepsgenoten worden gecommuniceerd?',
              ),
               'name' => 'onderwijs',
            ),
            3 =>
            (object) array(
               'value' => 'kinderopvang',
               'label' => 'Kinderopvang',
               'suggestions' =>
              array (
                0 => 'Welke locatie en koepelorganisatie?',
                1 => 'Is opvang verbonden aan een school? Zo ja, welke school?',
                2 => 'Welke groep(en)?',
                3 => 'Hoeveel kinderen/begeleiders op de groep?',
                4 => 'Wat is de rol van index op de locatie?',
                5 => 'Bij medewerker: wat is de functie?',
                6 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                7 => 'Bij medewerker: met welke personen was er nauw contact?',
                8 => 'Is de groep al in quarantaine en zo ja, vanaf wanneer?',
                9 => 'Is de locatie op de hoogte van de positieve testuitslag?',
                10 => 'Onrust op locatie?',
                11 => 'Mag naam index ook naar ouders worden gecommuniceerd?',
              ),
               'name' => 'kinderopvang',
            ),
            4 =>
            (object) array(
               'value' => 'religieuze_bijeenkomst',
               'label' => 'Religieuze bijeenkomst',
               'suggestions' =>
              array (
                0 => 'Beschrijving van de locatie en bijeenkomst?',
                1 => 'Vaste zitplaatsen of ook staande/lopende mensen?',
                2 => 'Is er gezongen?',
                3 => 'Welke ruimtes zijn er gebruikt?',
                4 => 'Met welke personen was er contact?',
                5 => 'Waren er pauzemomenten? Zo ja, zijn daarbij andere ruimtes gebruikt? Andere contacten (gezamenlijk gegeten/gedronken)?',
              ),
               'name' => 'religieuzeBijeenkomst',
            ),
            5 =>
            (object) array(
               'value' => 'koor',
               'label' => 'Koor',
               'suggestions' =>
              array (
                0 => 'Beschrijving van de locatie?',
                1 => 'Hoeveel personen waren aanwezig?',
                2 => 'Zijn er muziekinstrumenten gebruikt of alleen gezongen?',
                3 => 'Zijn muziekinstrumenten uitgewisseld?',
                4 => 'Waren er pauzemomenten? Zo ja, zijn daarbij andere ruimtes gebruikt? Andere contacten (gezamenlijk gegeten/gedronken)?',
              ),
               'name' => 'koor',
            ),
            6 =>
            (object) array(
               'value' => 'studentenverening',
               'label' => 'Studentenverening',
               'suggestions' =>
              array (
                0 => 'Beschrijving type activiteit en locatie?',
                1 => 'Zijn er nog andere locaties bezocht?',
                2 => 'Binnen of buiten?',
                3 => 'Hoeveel personen?',
                4 => 'Was er contact met andere studentenverenigingen?',
                5 => 'Wie is het aanspreekpunt/organisator?',
              ),
               'name' => 'studentenverening',
            ),
            7 =>
            (object) array(
               'value' => 'vereniging_overige',
               'label' => 'Overige verenigingen',
               'suggestions' =>
              array (
                0 => 'Omschrijving van de vereniging/locatie?',
                1 => 'Omschrijving van de activiteit?',
                2 => 'Hoeveel personen namen deel aan de activiteit?',
              ),
               'name' => 'verenigingOverige',
            ),
            8 =>
            (object) array(
               'value' => 'overige_andere_werkplek',
               'label' => 'Overige andere werkplek',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek/werkzaamheden?',
                1 => 'Binnen of buiten locatie?',
                2 => 'Gezamenlijke woonvoorziening?',
                3 => 'Gezamenlijk vervoer van/naar werk?',
                4 => 'Gezamenlijk overleg(gen)?',
                5 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                6 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                7 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'overigeAndereWerkplek',
            ),
            9 =>
            (object) array(
               'value' => 'vleesverwerking_slachthuis',
               'label' => 'Vleesverwerking / Slachthuis',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek?',
                1 => 'Subafdeling? (productie, schoonmaak, leiding, etc.)',
                2 => 'Gezamenlijke woonvoorziening?',
                3 => 'Gezamenlijk vervoer van/naar werk?',
                4 => 'Gezamenlijk overleg(gen)?',
                5 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                6 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                7 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'vleesverwerkingSlachthuis',
            ),
            10 =>
            (object) array(
               'value' => 'land_en_tuinbouw',
               'label' => 'Land- en tuinbouw',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek/werkzaamheden?',
                1 => 'Soort sector (agrarisch bedrijf, loondienst, tuinbouw)',
                2 => 'Werkzaamheden overdekt (tuinbouw) of in open lucht?',
                3 => 'Gezamenlijk vervoer van/naar werk?',
                4 => 'Gezamenlijk overleg(gen)?',
                5 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                6 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                7 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'landEnTuinbouw',
            ),
            11 =>
            (object) array(
               'value' => 'bouw',
               'label' => 'Bouw',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek/werkzaamheden?',
                1 => 'Werkzaamheden overdekt of in open lucht?',
                2 => 'Gezamenlijk vervoer van/naar werk?',
                3 => 'Gezamenlijk overleg(gen)?',
                4 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                5 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                6 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'bouw',
            ),
            12 =>
            (object) array(
               'value' => 'fabriek',
               'label' => 'Fabriek',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek/werkzaamheden?',
                1 => 'Subafdeling binnen fabriek?',
                2 => 'Gezamenlijke woonvoorziening?',
                3 => 'Gezamenlijk vervoer van/naar werk?',
                4 => 'Gezamenlijk overleg(gen)?',
                5 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                6 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                7 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'fabriek',
            ),
            13 =>
            (object) array(
               'value' => 'kantoor_overige_branche',
               'label' => 'Kantoor overige branche',
               'suggestions' =>
              array (
                0 => 'Omschrijving werkplek/werkzaamheden?',
                1 => 'Subafdeling binnen kantoor?',
                2 => 'Gezamenlijk vervoer van/naar werk?',
                3 => 'Gezamenlijk overleg(gen)?',
                4 => 'Gezamenlijke lunch/pauze/rookplek etc.?',
                5 => 'Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?',
                6 => 'Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?',
              ),
               'name' => 'kantoorOverigeBranche',
            ),
            14 =>
            (object) array(
               'value' => 'horeca',
               'label' => 'Horeca',
               'suggestions' =>
              array (
                0 => 'Welk type setting? (Dancing, bar, restaurant, etc.)',
                1 => 'Bezoeker of medewerker?',
                2 => 'Bij medewerker: wat is de functie?',
                3 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                4 => 'Bij medewerker: met welke personen was er nauw contact?',
                5 => 'Betreft het een gelegenheid binnen of buiten?',
                6 => 'Is er gewerkt met de CoronaCheck-app?',
                7 => 'Verplichte reservering of vrije inloop?',
                8 => 'Hoeveel personen aan tafel?',
                9 => 'Selfservice of bediening?',
                10 => 'Mogelijkheid tot het houden van afstand tot medewerkers?',
                11 => 'Zijn er contactgegevens bijgehouden van bezoekers?',
              ),
               'name' => 'horeca',
            ),
            15 =>
            (object) array(
               'value' => 'horeca_overig',
               'label' => 'Horeca overig',
               'suggestions' =>
              array (
                0 => 'Omschrijving van de locatie?',
                1 => 'Werkt op afspraak of vrije inloop?',
                2 => 'Zijn er contactgegevens bijgehouden van bezoekers?',
                3 => 'Bij medewerker: wat is de functie?',
                4 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                5 => 'Bij medewerker: met welke personen was er nauw contact?',
                6 => 'Bij contactberoep: is er gebruik gemaakt van PBM? Zo ja, zijn die op de correcte wijze gebruikt?',
              ),
               'name' => 'horecaOverig',
            ),
            16 =>
            (object) array(
               'value' => 'buitenland',
               'label' => 'Buitenland reis',
               'suggestions' =>
              array (
                0 => 'Welk(e) land(en) bezocht?',
                1 => 'Datum vertrek en aankomst?',
                2 => 'Beschrijving van de accommodatie?',
                3 => 'Welk vervoermiddel? Eigen vervoer?',
                4 => 'Is er gereisd met een gezelschap? Wat was de samenstelling van dit gezelschap? (leeftijden/aantal personen/relatie tussen groepsleden, etc.)',
                5 => 'Indien georganiseerde reis: wie is aanspreekpunt/touroperator?',
              ),
               'name' => 'buitenland',
            ),
            17 =>
            (object) array(
               'value' => 'zee_transport',
               'label' => 'Schip / Zee- en binnenvaart / Haven',
               'suggestions' =>
              array (
                0 => 'Type schip: passagiersvaart of beroepsvaart?',
                1 => 'Wat was de grootte van de bemanning? (Voor beroepsvaart >20 personen gelden andere maatregelen)',
                2 => 'Passagier of bemanningslid?',
                3 => 'Overnachting op het schip? Zo ja, wie waren de hutgenoten?',
                4 => 'Inrichting van leefruimtes op het schip?',
                5 => 'Locatie van schip (onderweg, haven, ankerplaats, etc.)?',
                6 => 'Plannen voor de nabije toekomst (vertrekdatum, laden/lossen, volgende bestemming)?',
                7 => 'Gebruik van een loods?',
                8 => 'Was er tijdens de reis (intensief) contact met medewerkers of passagiers? (bijvoorbeeld medische zorg verleend, reisgezelschap) Beschrijf dit contact',
              ),
               'name' => 'zeeTransport',
            ),
            18 =>
            (object) array(
               'value' => 'vlieg_transport',
               'label' => 'Vliegreis / Luchthaven',
               'suggestions' =>
              array (
                0 => 'Luchthaven van vertrek (stad) en aankomst?',
                1 => 'Bij transfer: in welk land begon de reis en welke landen zijn onderweg aangedaan?',
                2 => 'Vertrekdatum en aankomstdatum van de vlucht naar Nederland?',
                3 => 'Luchtvaartmaatschappij?',
                4 => 'Vluchtnummer?',
                5 => 'Bij bemanningslid: functie en werkzaamheden?',
                6 => 'Bij passagier: stoelnummer? Zat de index op de aangewezen stoel?',
                7 => 'Bij passagier: is er gereisd met een gezelschap? Wat was de samenstelling van dit gezelschap? (leeftijden/aantal personen/relatie tussen groepsleden, etc.)',
                8 => 'Bij een georganiseerde reis: wie was is het aanspreekpunt/touroperator? (i.v.m. uitvragen passagierslijsten)',
                9 => 'Klachten tijdens de vlucht? Zo ja, welke klachten?',
                10 => 'Was er nauw contact (zoals medische verzorging)? Zo ja, omschrijf dit contact.',
              ),
               'name' => 'vliegTransport',
            ),
            19 =>
            (object) array(
               'value' => 'reizen_vervoer_overig',
               'label' => 'Overig transport',
               'suggestions' =>
              array (
                0 => 'Plaats van vertrek en aankomst?',
                1 => 'Datum van vertrek en aankomst?',
                2 => 'Welke vervoersmiddel is er gebruikt?',
                3 => 'Welke vervoersmaatschappij?',
                4 => 'Klachten tijdens de reis? Zo ja, welke klachten?',
                5 => 'Trein/busnummer, zitplaats, rij, coupé? Was er een gereserveerde zitplaats?',
                6 => 'Was er nauw contact? Zo ja, omschrijving van contact',
                7 => 'Is er in gezelschap gereisd? Wat was de samenstelling van de groep (leeftijd/aantal personen /relatie tussen groepsleden)?',
              ),
               'name' => 'reizenVervoerOverig',
            ),
            20 =>
            (object) array(
               'value' => 'zorg',
               'label' => 'Zorg',
               'suggestions' =>
              array (
                0 => 'Welke locatie en koepelorganisatie?',
                1 => 'Welke afdeling(en), wijkteam(s) of woning(en)? Op (hoofd)kantoor geweest?',
                2 => 'Welke data aanwezig op afdeling(en), woning(en) of bij wijkteam(s) - noteer data per afdeling?',
                3 => 'Open of gesloten afdeling(en)?',
                4 => 'Welke rol had de index op locatie? Zorgmedewerker, thuiszorgmedewerker, bewoner, bezoeker, etc.?',
                5 => 'Is er gebruik gemaakt van PBM? (Medisch mondmasker (type II of IIR, FFP1, FFP2) + handschoenen) Zo ja, zijn die op de correcte wijze gebruikt?',
                6 => 'Bij zorgmedewerker: wat is de functie? (arts, verpleegkundige, fysiotherapeut etc.)',
                7 => 'Bij zorgmedewerker: welke werkzaamheden zijn er uitgevoerd? (kantoorwerk, directe zorg etc.)',
                8 => 'Bij zorgmedewerker: met welke collega’s was er nauw contact tijdens welke activiteiten? (namen van collega’s)',
                9 => 'Bij zorgmedewerker: een eigen arbodienst of ZZP?',
                10 => 'Bij cliënt: welke zorg heeft index ontvangen?',
                11 => 'Bij bezoeker (poli)kliniek: welke zorg heeft index ontvangen?',
                12 => 'Bij bezoeker (poli)kliniek: naam, datum, tijdstip en wachtkamer van bezoek?',
              ),
               'name' => 'zorg',
            ),
            21 =>
            (object) array(
               'value' => 'zorg_overig',
               'label' => 'Zorg overig',
               'suggestions' =>
              array (
                0 => 'Welke locatie en koepelorganisatie?',
                1 => 'Welke afdeling(en)?',
                2 => 'Welke data aanwezig op afdeling(en)? (data per afdeling specifiek noemen)',
                3 => 'Is er gebruik gemaakt van PBM? (Medisch mondmasker (type II of IIR, FFP1, FFP2) + handschoenen) Zo ja, zijn die op de correcte wijze gebruikt?',
                4 => 'Bij zorgmedewerker: wat is de exacte functie? (arts, verpleegkundige, fysiotherapeut etc.)',
                5 => 'Bij zorgmedewerker: welke werkzaamheden zijn er uitgevoerd? (kantoorwerk, directe zorg etc.)',
                6 => 'Bij zorgmedewerker: met welke collega’s was er nauw contact tijdens welke activiteiten? (namen van collega’s)',
                7 => 'Bij zorgmedewerker: een eigen arbodienst of ZZP?',
                8 => 'Bij cliënt: welke zorg heeft index ontvangen?',
                9 => 'Bij bezoeker (poli)kliniek: welke zorg heeft index ontvangen?',
                10 => 'Bij bezoeker (poli)kliniek: naam, datum, tijdstip en wachtkamer van bezoek?',
              ),
               'name' => 'zorgOverig',
            ),
            22 =>
            (object) array(
               'value' => 'evenement',
               'label' => 'Evenement',
               'suggestions' =>
              array (
                0 => 'Wat voor soort evenement?',
                1 => 'Overdekt of in de open lucht?',
                2 => 'Is er gezongen of geschreeuwd?',
                3 => 'Zijn er ontmoetingsruimtes (bijvoorbeeld kiosk of kantine) aanwezig?',
                4 => 'Zijn er contactgegevens bijgehouden van bezoekers?',
                5 => 'Bij medewerker/vrijwilliger: wat is de functie?',
                6 => 'Bij medewerker/vrijwilliger: welke werkzaamheden zijn er uitgevoerd?',
                7 => 'Bij medewerker/vrijwilliger: locatie werkzaamheden?',
                8 => 'Bij bezoeker: welke zaal/vak/tribune?',
                9 => 'Bij bezoeker: welk stoelnummer?',
                10 => 'Gaat het om een testevenement?',
                11 => 'Bij Testen voor Toegang: welk evenement? Noteer naam en datum.',
                12 => 'Bij Testen voor Toegang: was de index besmettelijk ten tijde van het evenement?',
                13 => 'Bij Testen voor Toegang: is er een andere aannemelijke bron voor infectie dan het evenement, zo ja welke?',
              ),
               'name' => 'evenement',
            ),
            23 =>
            (object) array(
               'value' => 'maatschappelijke_opvang',
               'label' => 'Maatschappelijke opvang',
               'suggestions' =>
              array (
                0 => 'Type opvang?',
                1 => 'Open of gesloten opvang?',
                2 => 'Welke rol had de index op locatie? Medewerker, bewoner, bezoeker, etc.?',
                3 => 'Welke contacten had de index met personeelsleden, bezoekers en bewoners?',
                4 => 'Gezamenlijke activiteiten? Zo ja, beschrijving van de activiteiten',
                5 => 'Groepsgrootte?',
                6 => 'Hoe zijn de woon-, slaap- en sanitaire voorzieningen ingedeeld?',
                7 => 'Met wie worden deze voorzieningen gedeeld?',
                8 => 'Welke groepsruimtes zijn er gebruikt?',
                9 => 'Bij medewerker: wat is de functie?',
                10 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                11 => 'Bij medewerker: met welke personen was er nauw contact?',
              ),
               'name' => 'maatschappelijkeOpvang',
            ),
            24 =>
            (object) array(
               'value' => 'thuissituatie_bezoek',
               'label' => 'Thuissituatie / bezoek',
               'suggestions' =>
              array (
                0 => 'Wat was de gelegenheid?',
                1 => 'Was het binnen of buiten?',
                2 => 'Omschrijving van de locatie.',
                3 => 'Wat was de samenstelling van het gezelschap?',
                4 => 'Was er sprake van nauw/lichamelijk contact (omhelzing etc.)?',
                5 => 'Is er gezongen/gejuicht? Zo ja, hoelang duurde dit?',
                6 => 'Wie is aanspreekpunt/organisator?',
              ),
               'name' => 'thuissituatieBezoek',
            ),
            25 =>
            (object) array(
               'value' => 'accommodatie',
               'label' => 'Accommodatie',
               'suggestions' =>
              array (
                0 => 'Wat voor locatie? (hotel, B&B, camping etc.)',
                1 => 'Gebruik gemaakt van gedeelde faciliteiten (gedeeld sanitair, spa, restaurant, bowlingbaan etc.)?',
                2 => 'Is de accommodatie op de hoogte van de besmetting?',
                3 => 'Bij medewerker: wat is de functie?',
                4 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                5 => 'Bij medewerker: met welke personen was er nauw contact?',
              ),
               'name' => 'accommodatie',
            ),
            26 =>
            (object) array(
               'value' => 'retail',
               'label' => 'Retail',
               'suggestions' =>
              array (
                0 => 'Omschrijving van de locatie?',
                1 => 'Wordt er gewerkt op afspraak of vrije inloop?',
                2 => 'Zijn er contactgegevens bijgehouden van bezoekers?',
                3 => 'Bij medewerker: wat is de functie?',
                4 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                5 => 'Bij medewerker: met welke personen was er nauw contact?',
              ),
               'name' => 'retail',
            ),
            27 =>
            (object) array(
               'value' => 'zwembad',
               'label' => 'Zwembad',
               'suggestions' =>
              array (
                0 => 'Binnen- of buitenlocatie?',
                1 => 'Groepskleedkamers of individuele kleedkamers?',
                2 => 'Zijn er contactgegevens bijgehouden?',
                3 => 'Bij medewerker: wat is de functie?',
                4 => 'Bij medewerker: welke werkzaamheden zijn er uitgevoerd?',
                5 => 'Bij medewerker: met welke personen was er nauw contact?',
              ),
               'name' => 'zwembad',
            ),
          ),
        );
    }
}
