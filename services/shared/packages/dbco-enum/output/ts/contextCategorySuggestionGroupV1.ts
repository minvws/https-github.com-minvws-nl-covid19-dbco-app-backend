/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategorySuggestionGroup.json!
 */

/**
 * Context category suggestion groups. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContextCategorySuggestionGroupV1 {
  'VALUE_overig' = 'overig',
  'VALUE_sport' = 'sport',
  'VALUE_onderwijs' = 'onderwijs',
  'VALUE_kinderopvang' = 'kinderopvang',
  'VALUE_religieuze_bijeenkomst' = 'religieuze_bijeenkomst',
  'VALUE_koor' = 'koor',
  'VALUE_studentenverening' = 'studentenverening',
  'VALUE_vereniging_overige' = 'vereniging_overige',
  'VALUE_overige_andere_werkplek' = 'overige_andere_werkplek',
  'VALUE_vleesverwerking_slachthuis' = 'vleesverwerking_slachthuis',
  'VALUE_land_en_tuinbouw' = 'land_en_tuinbouw',
  'VALUE_bouw' = 'bouw',
  'VALUE_fabriek' = 'fabriek',
  'VALUE_kantoor_overige_branche' = 'kantoor_overige_branche',
  'VALUE_horeca' = 'horeca',
  'VALUE_horeca_overig' = 'horeca_overig',
  'VALUE_buitenland' = 'buitenland',
  'VALUE_zee_transport' = 'zee_transport',
  'VALUE_vlieg_transport' = 'vlieg_transport',
  'VALUE_reizen_vervoer_overig' = 'reizen_vervoer_overig',
  'VALUE_zorg' = 'zorg',
  'VALUE_zorg_overig' = 'zorg_overig',
  'VALUE_evenement' = 'evenement',
  'VALUE_maatschappelijke_opvang' = 'maatschappelijke_opvang',
  'VALUE_thuissituatie_bezoek' = 'thuissituatie_bezoek',
  'VALUE_accommodatie' = 'accommodatie',
  'VALUE_retail' = 'retail',
  'VALUE_zwembad' = 'zwembad',
}

/**
 * Context category suggestion groups. options to be used in the forms
 */
export const contextCategorySuggestionGroupV1Options = [
    {
        "label": "Overig",
        "value": ContextCategorySuggestionGroupV1.VALUE_overig,
        "suggestions": [
            "Type bijeenkomst? Duur?",
            "Was het een georganiseerde bijeenkomst of een niet-georganiseerde bijeenkomst?",
            "Nauw/lichamelijk contact (bijv. omhelzing)?",
            "Zijn er mensen uit één sociale omgeving (werk/klas/opleiding) of uit meerdere sociale omgevingen aanwezig? Hoe groot was de groep?",
            "Was het continu dezelfde groep, of was er een constante wisseling van de groep mensen?",
            "Zijn er onregelmatigheden geweest? Was er sprake van fysiek contact?"
        ]
    },
    {
        "label": "Sport",
        "value": ContextCategorySuggestionGroupV1.VALUE_sport,
        "suggestions": [
            "Beschrijving van de locatie/sportruimte?",
            "Welke sport(les) is er beoefend/gegeven?",
            "Betreft het een contactsport?",
            "Betreft het een groepssport? Zo ja, wat was de groepsgrootte?",
            "Gaat het om binnen- of buitensport?",
            "Is er gebruikgemaakt van kleedkamers? Zo ja, waren dit groepskleedkamers of individuele kleedkamers?"
        ]
    },
    {
        "label": "Onderwijs",
        "value": ContextCategorySuggestionGroupV1.VALUE_onderwijs,
        "suggestions": [
            "Welke onderwijsvorm?",
            "Welke locatie en koepelorganisatie?",
            "Bij PO of VO: welke klas(sen), groep(en) en/of leerjaar?",
            "Bij HBO of universiteit: welke afdeling, opleiding en/of leerjaar?",
            "Meerdere locaties? Zo ja, welke?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?",
            "Bij student/leerling: bij welke docenten en/of medestudenten/leerlingen in de buurt geweest tijdens besmettelijke periode, noteer per dag (namen)?",
            "Bij student/leerling: welke lessen gevolgd tijdens besmettelijke periode per dag? (denk ook aan: plusgroepen, extra lessen, huiswerkklas)",
            "Zit klas of groep al (deels) in quarantaine en zo ja, vanaf wanneer?",
            "Aantal leerlingen/studenten in klas en in bubbel?",
            "Positieve uitslag bekend bij school?",
            "Onrust over COVID-19 beleid in de school?",
            "Is er gebruik gemaakt van gezamenlijk leerlingenvervoer? (NSO/SO)",
            "Mag naam index ook naar klas- of groepsgenoten worden gecommuniceerd?"
        ]
    },
    {
        "label": "Kinderopvang",
        "value": ContextCategorySuggestionGroupV1.VALUE_kinderopvang,
        "suggestions": [
            "Welke locatie en koepelorganisatie?",
            "Is opvang verbonden aan een school? Zo ja, welke school?",
            "Welke groep(en)?",
            "Hoeveel kinderen/begeleiders op de groep?",
            "Wat is de rol van index op de locatie?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?",
            "Is de groep al in quarantaine en zo ja, vanaf wanneer?",
            "Is de locatie op de hoogte van de positieve testuitslag?",
            "Onrust op locatie?",
            "Mag naam index ook naar ouders worden gecommuniceerd?"
        ]
    },
    {
        "label": "Religieuze bijeenkomst",
        "value": ContextCategorySuggestionGroupV1.VALUE_religieuze_bijeenkomst,
        "suggestions": [
            "Beschrijving van de locatie en bijeenkomst?",
            "Vaste zitplaatsen of ook staande/lopende mensen?",
            "Is er gezongen?",
            "Welke ruimtes zijn er gebruikt?",
            "Met welke personen was er contact?",
            "Waren er pauzemomenten? Zo ja, zijn daarbij andere ruimtes gebruikt? Andere contacten (gezamenlijk gegeten/gedronken)?"
        ]
    },
    {
        "label": "Koor",
        "value": ContextCategorySuggestionGroupV1.VALUE_koor,
        "suggestions": [
            "Beschrijving van de locatie?",
            "Hoeveel personen waren aanwezig?",
            "Zijn er muziekinstrumenten gebruikt of alleen gezongen?",
            "Zijn muziekinstrumenten uitgewisseld?",
            "Waren er pauzemomenten? Zo ja, zijn daarbij andere ruimtes gebruikt? Andere contacten (gezamenlijk gegeten/gedronken)?"
        ]
    },
    {
        "label": "Studentenverening",
        "value": ContextCategorySuggestionGroupV1.VALUE_studentenverening,
        "suggestions": [
            "Beschrijving type activiteit en locatie?",
            "Zijn er nog andere locaties bezocht?",
            "Binnen of buiten?",
            "Hoeveel personen?",
            "Was er contact met andere studentenverenigingen?",
            "Wie is het aanspreekpunt/organisator?"
        ]
    },
    {
        "label": "Overige verenigingen",
        "value": ContextCategorySuggestionGroupV1.VALUE_vereniging_overige,
        "suggestions": [
            "Omschrijving van de vereniging/locatie?",
            "Omschrijving van de activiteit?",
            "Hoeveel personen namen deel aan de activiteit?"
        ]
    },
    {
        "label": "Overige andere werkplek",
        "value": ContextCategorySuggestionGroupV1.VALUE_overige_andere_werkplek,
        "suggestions": [
            "Omschrijving werkplek/werkzaamheden?",
            "Binnen of buiten locatie?",
            "Gezamenlijke woonvoorziening?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Vleesverwerking / Slachthuis",
        "value": ContextCategorySuggestionGroupV1.VALUE_vleesverwerking_slachthuis,
        "suggestions": [
            "Omschrijving werkplek?",
            "Subafdeling? (productie, schoonmaak, leiding, etc.)",
            "Gezamenlijke woonvoorziening?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Land- en tuinbouw",
        "value": ContextCategorySuggestionGroupV1.VALUE_land_en_tuinbouw,
        "suggestions": [
            "Omschrijving werkplek/werkzaamheden?",
            "Soort sector (agrarisch bedrijf, loondienst, tuinbouw)",
            "Werkzaamheden overdekt (tuinbouw) of in open lucht?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Bouw",
        "value": ContextCategorySuggestionGroupV1.VALUE_bouw,
        "suggestions": [
            "Omschrijving werkplek/werkzaamheden?",
            "Werkzaamheden overdekt of in open lucht?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Fabriek",
        "value": ContextCategorySuggestionGroupV1.VALUE_fabriek,
        "suggestions": [
            "Omschrijving werkplek/werkzaamheden?",
            "Subafdeling binnen fabriek?",
            "Gezamenlijke woonvoorziening?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Kantoor overige branche",
        "value": ContextCategorySuggestionGroupV1.VALUE_kantoor_overige_branche,
        "suggestions": [
            "Omschrijving werkplek/werkzaamheden?",
            "Subafdeling binnen kantoor?",
            "Gezamenlijk vervoer van/naar werk?",
            "Gezamenlijk overleg(gen)?",
            "Gezamenlijke lunch/pauze/rookplek etc.?",
            "Werkt index als uitzendkracht? Zo ja, welke organisatie en welke contactpersoon?",
            "Bij uitzendkracht: is er toestemming om contact op te nemen met het uitzendbureau?"
        ]
    },
    {
        "label": "Horeca",
        "value": ContextCategorySuggestionGroupV1.VALUE_horeca,
        "suggestions": [
            "Welk type setting? (Dancing, bar, restaurant, etc.)",
            "Bezoeker of medewerker?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?",
            "Betreft het een gelegenheid binnen of buiten?",
            "Is er gewerkt met de CoronaCheck-app?",
            "Verplichte reservering of vrije inloop?",
            "Hoeveel personen aan tafel?",
            "Selfservice of bediening?",
            "Mogelijkheid tot het houden van afstand tot medewerkers?",
            "Zijn er contactgegevens bijgehouden van bezoekers?"
        ]
    },
    {
        "label": "Horeca overig",
        "value": ContextCategorySuggestionGroupV1.VALUE_horeca_overig,
        "suggestions": [
            "Omschrijving van de locatie?",
            "Werkt op afspraak of vrije inloop?",
            "Zijn er contactgegevens bijgehouden van bezoekers?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?",
            "Bij contactberoep: is er gebruik gemaakt van PBM? Zo ja, zijn die op de correcte wijze gebruikt?"
        ]
    },
    {
        "label": "Buitenland reis",
        "value": ContextCategorySuggestionGroupV1.VALUE_buitenland,
        "suggestions": [
            "Welk(e) land(en) bezocht?",
            "Datum vertrek en aankomst?",
            "Beschrijving van de accommodatie?",
            "Welk vervoermiddel? Eigen vervoer?",
            "Is er gereisd met een gezelschap? Wat was de samenstelling van dit gezelschap? (leeftijden/aantal personen/relatie tussen groepsleden, etc.)",
            "Indien georganiseerde reis: wie is aanspreekpunt/touroperator?"
        ]
    },
    {
        "label": "Schip / Zee- en binnenvaart / Haven",
        "value": ContextCategorySuggestionGroupV1.VALUE_zee_transport,
        "suggestions": [
            "Type schip: passagiersvaart of beroepsvaart?",
            "Wat was de grootte van de bemanning? (Voor beroepsvaart >20 personen gelden andere maatregelen)",
            "Passagier of bemanningslid?",
            "Overnachting op het schip? Zo ja, wie waren de hutgenoten?",
            "Inrichting van leefruimtes op het schip?",
            "Locatie van schip (onderweg, haven, ankerplaats, etc.)?",
            "Plannen voor de nabije toekomst (vertrekdatum, laden/lossen, volgende bestemming)?",
            "Gebruik van een loods?",
            "Was er tijdens de reis (intensief) contact met medewerkers of passagiers? (bijvoorbeeld medische zorg verleend, reisgezelschap) Beschrijf dit contact"
        ]
    },
    {
        "label": "Vliegreis / Luchthaven",
        "value": ContextCategorySuggestionGroupV1.VALUE_vlieg_transport,
        "suggestions": [
            "Luchthaven van vertrek (stad) en aankomst?",
            "Bij transfer: in welk land begon de reis en welke landen zijn onderweg aangedaan?",
            "Vertrekdatum en aankomstdatum van de vlucht naar Nederland?",
            "Luchtvaartmaatschappij?",
            "Vluchtnummer?",
            "Bij bemanningslid: functie en werkzaamheden?",
            "Bij passagier: stoelnummer? Zat de index op de aangewezen stoel?",
            "Bij passagier: is er gereisd met een gezelschap? Wat was de samenstelling van dit gezelschap? (leeftijden/aantal personen/relatie tussen groepsleden, etc.)",
            "Bij een georganiseerde reis: wie was is het aanspreekpunt/touroperator? (i.v.m. uitvragen passagierslijsten)",
            "Klachten tijdens de vlucht? Zo ja, welke klachten?",
            "Was er nauw contact (zoals medische verzorging)? Zo ja, omschrijf dit contact."
        ]
    },
    {
        "label": "Overig transport",
        "value": ContextCategorySuggestionGroupV1.VALUE_reizen_vervoer_overig,
        "suggestions": [
            "Plaats van vertrek en aankomst?",
            "Datum van vertrek en aankomst?",
            "Welke vervoersmiddel is er gebruikt?",
            "Welke vervoersmaatschappij?",
            "Klachten tijdens de reis? Zo ja, welke klachten?",
            "Trein/busnummer, zitplaats, rij, coupé? Was er een gereserveerde zitplaats?",
            "Was er nauw contact? Zo ja, omschrijving van contact",
            "Is er in gezelschap gereisd? Wat was de samenstelling van de groep (leeftijd/aantal personen /relatie tussen groepsleden)?"
        ]
    },
    {
        "label": "Zorg",
        "value": ContextCategorySuggestionGroupV1.VALUE_zorg,
        "suggestions": [
            "Welke locatie en koepelorganisatie?",
            "Welke afdeling(en), wijkteam(s) of woning(en)? Op (hoofd)kantoor geweest?",
            "Welke data aanwezig op afdeling(en), woning(en) of bij wijkteam(s) - noteer data per afdeling?",
            "Open of gesloten afdeling(en)?",
            "Welke rol had de index op locatie? Zorgmedewerker, thuiszorgmedewerker, bewoner, bezoeker, etc.?",
            "Is er gebruik gemaakt van PBM? (Medisch mondmasker (type II of IIR, FFP1, FFP2) + handschoenen) Zo ja, zijn die op de correcte wijze gebruikt?",
            "Bij zorgmedewerker: wat is de functie? (arts, verpleegkundige, fysiotherapeut etc.)",
            "Bij zorgmedewerker: welke werkzaamheden zijn er uitgevoerd? (kantoorwerk, directe zorg etc.)",
            "Bij zorgmedewerker: met welke collega’s was er nauw contact tijdens welke activiteiten? (namen van collega’s)",
            "Bij zorgmedewerker: een eigen arbodienst of ZZP?",
            "Bij cliënt: welke zorg heeft index ontvangen?",
            "Bij bezoeker (poli)kliniek: welke zorg heeft index ontvangen?",
            "Bij bezoeker (poli)kliniek: naam, datum, tijdstip en wachtkamer van bezoek?"
        ]
    },
    {
        "label": "Zorg overig",
        "value": ContextCategorySuggestionGroupV1.VALUE_zorg_overig,
        "suggestions": [
            "Welke locatie en koepelorganisatie?",
            "Welke afdeling(en)?",
            "Welke data aanwezig op afdeling(en)? (data per afdeling specifiek noemen)",
            "Is er gebruik gemaakt van PBM? (Medisch mondmasker (type II of IIR, FFP1, FFP2) + handschoenen) Zo ja, zijn die op de correcte wijze gebruikt?",
            "Bij zorgmedewerker: wat is de exacte functie? (arts, verpleegkundige, fysiotherapeut etc.)",
            "Bij zorgmedewerker: welke werkzaamheden zijn er uitgevoerd? (kantoorwerk, directe zorg etc.)",
            "Bij zorgmedewerker: met welke collega’s was er nauw contact tijdens welke activiteiten? (namen van collega’s)",
            "Bij zorgmedewerker: een eigen arbodienst of ZZP?",
            "Bij cliënt: welke zorg heeft index ontvangen?",
            "Bij bezoeker (poli)kliniek: welke zorg heeft index ontvangen?",
            "Bij bezoeker (poli)kliniek: naam, datum, tijdstip en wachtkamer van bezoek?"
        ]
    },
    {
        "label": "Evenement",
        "value": ContextCategorySuggestionGroupV1.VALUE_evenement,
        "suggestions": [
            "Wat voor soort evenement?",
            "Overdekt of in de open lucht?",
            "Is er gezongen of geschreeuwd?",
            "Zijn er ontmoetingsruimtes (bijvoorbeeld kiosk of kantine) aanwezig?",
            "Zijn er contactgegevens bijgehouden van bezoekers?",
            "Bij medewerker/vrijwilliger: wat is de functie?",
            "Bij medewerker/vrijwilliger: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker/vrijwilliger: locatie werkzaamheden?",
            "Bij bezoeker: welke zaal/vak/tribune?",
            "Bij bezoeker: welk stoelnummer?",
            "Gaat het om een testevenement?",
            "Bij Testen voor Toegang: welk evenement? Noteer naam en datum.",
            "Bij Testen voor Toegang: was de index besmettelijk ten tijde van het evenement?",
            "Bij Testen voor Toegang: is er een andere aannemelijke bron voor infectie dan het evenement, zo ja welke?"
        ]
    },
    {
        "label": "Maatschappelijke opvang",
        "value": ContextCategorySuggestionGroupV1.VALUE_maatschappelijke_opvang,
        "suggestions": [
            "Type opvang?",
            "Open of gesloten opvang?",
            "Welke rol had de index op locatie? Medewerker, bewoner, bezoeker, etc.?",
            "Welke contacten had de index met personeelsleden, bezoekers en bewoners?",
            "Gezamenlijke activiteiten? Zo ja, beschrijving van de activiteiten",
            "Groepsgrootte?",
            "Hoe zijn de woon-, slaap- en sanitaire voorzieningen ingedeeld?",
            "Met wie worden deze voorzieningen gedeeld?",
            "Welke groepsruimtes zijn er gebruikt?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?"
        ]
    },
    {
        "label": "Thuissituatie / bezoek",
        "value": ContextCategorySuggestionGroupV1.VALUE_thuissituatie_bezoek,
        "suggestions": [
            "Wat was de gelegenheid?",
            "Was het binnen of buiten?",
            "Omschrijving van de locatie.",
            "Wat was de samenstelling van het gezelschap?",
            "Was er sprake van nauw/lichamelijk contact (omhelzing etc.)?",
            "Is er gezongen/gejuicht? Zo ja, hoelang duurde dit?",
            "Wie is aanspreekpunt/organisator?"
        ]
    },
    {
        "label": "Accommodatie",
        "value": ContextCategorySuggestionGroupV1.VALUE_accommodatie,
        "suggestions": [
            "Wat voor locatie? (hotel, B&B, camping etc.)",
            "Gebruik gemaakt van gedeelde faciliteiten (gedeeld sanitair, spa, restaurant, bowlingbaan etc.)?",
            "Is de accommodatie op de hoogte van de besmetting?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?"
        ]
    },
    {
        "label": "Retail",
        "value": ContextCategorySuggestionGroupV1.VALUE_retail,
        "suggestions": [
            "Omschrijving van de locatie?",
            "Wordt er gewerkt op afspraak of vrije inloop?",
            "Zijn er contactgegevens bijgehouden van bezoekers?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?"
        ]
    },
    {
        "label": "Zwembad",
        "value": ContextCategorySuggestionGroupV1.VALUE_zwembad,
        "suggestions": [
            "Binnen- of buitenlocatie?",
            "Groepskleedkamers of individuele kleedkamers?",
            "Zijn er contactgegevens bijgehouden?",
            "Bij medewerker: wat is de functie?",
            "Bij medewerker: welke werkzaamheden zijn er uitgevoerd?",
            "Bij medewerker: met welke personen was er nauw contact?"
        ]
    }
];
