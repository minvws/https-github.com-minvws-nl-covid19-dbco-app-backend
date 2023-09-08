<?php

declare(strict_types=1);

use App\Schema\Validation\ValidationMessageFormatter;

// Custom messages and attributes should contain their fragment identier to support
// retrieving multiple fragments in one request. A copy of the custom messages and
// attributes is made (stripping the fragment identifier) to support retrieval
// of one fragment.

$custom = [
    'index.dateOfBirth' => [
        'before_or_equal' => ':Attribute mag niet na deze datum liggen: :date.',
        'after_or_equal' => ':Attribute mag niet voor deze datum liggen: :date.',
    ],
    'index.bsn' => [
        'min' => ':Attribute is 7 cijfers lang.',
        'max' => ':Attribute is 7 cijfers lang.',
    ],
    'deceased.deceasedAt' => [
        'before_or_equal' => ':Attribute mag niet na deze datum liggen: :date.',
        'declined' => 'Deze persoon is een zorgmedewerker',
    ],
    'test.previousInfectionDateOfSymptom' => [
        'before' => ':Attribute moet eerder zijn dan: :date',
    ],
    'test.selfTestLabTestDate' => [
        'required_if' => ':Attribute is nodig voor Osiris indien het type zelftest gelijk is aan :value.',
    ],
    'test.previousInfectionCaseNumber' => [
        'regex' => 'Dit is geen geldig dossiernummer (AB1-234-567) of geldig HPZone-nummer (6 tot 8 cijfers)',
    ],
    'test.previousInfectionHpzoneNumber' => [
        'digits_between' => 'Let op: dit is geen geldig HPZone-nummer. Een HPZone-nummer bestaat uit 6 tot 8 cijfers.',
    ],
    'general.dateOfLastExposure' => [
        'before_or_equal' => ':Attribute moet gelijk of eerder zijn dan :date',
        'after_or_equal' => ':Attribute moet later zijn dan :date',
        'prohibited' => ':Attribute kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
    ],
    'general.context' => ['max' => 'Notitie mag niet uit meer dan :max tekens bestaan.'],
    'general.nature' => ['max' => 'Dit mag niet uit meer dan :max tekens bestaan.'],
    'general.reference' => [
        'digits' => 'Het BCO nummer moet uit 7 of 8 cijfers bestaan.',
        'unique' => 'Er bestaat al een case met dit BCO nummer',
        'required' => 'BCO nummer is verplicht.',
    ],
    'general.hpzoneNumber' => [
        'required_without' => ':Attribute is verplicht als Monsternummer niet ingevuld is.',
        'digits_between' => ':Attribute moet uit 7 of 8 cijfers bestaan.',
        'unique' => 'Er bestaat al een case met dit HPZone nummer',
    ],
    'general.moments.*.day' => [
        'before_or_equal' => 'Let op: deze datum moet gelijk of eerder zijn dan :date',
        'prohibited' => 'Let op: datum kan nog niet gekozen worden wegens ontbrekende medische gegevens in de case',
    ],
    'general.moments.*.startTime' => [
        'prohibited_if' => ':attribute kan niet worden ingevuld wanneer :other leeg is.',
    ],
    'general.moments.*.endTime' => [
        'after' => 'De tot-tijd moet na de vanaf-tijd zijn.',
        'prohibited_if' => ':attribute kan niet worden ingevuld wanneer :other leeg is.',
    ],
    'hospital.reason' => [
        'required_if' => 'Reden van opname dient ingevuld te worden.',
    ],
    'hospital.isInICU' => [
        'required_if' => 'Opname in IC dient ingevuld te worden.',
    ],
    'recentBirth.birthDate' => [
        'before_or_equal' => 'De geboortedatum mag niet na deze datum liggen: :date.',
        'after_or_equal' => 'De geboortedatum mag niet voor deze datum liggen: :date.',
    ],
    'pregancy.dueDate' => [
        'before_or_equal' => 'De uitgerekende datum ligt te ver in de toekomst.',
        'after_or_equal' => 'De uitgerekende datum mag niet ver in het verleden liggen.',
    ],
    'vaccination.vaccinationCount' => [
        'min' => 'Aantal vaccinaties mag niet lager zijn dan nul.',
        'max' => 'Aantal vaccinaties mag niet meer zijn dan :max.',
    ],
    'vaccination.vaccineInjections.*.injectionDate' => [
        'before_or_equal' => 'De prikdatum mag niet in de toekomst liggen.',
        'after_or_equal' => 'De prikdatum moet na :date zijn.',
    ],
    'abroad.trips.*.departureDate' => [
        'before_or_equal' => ':Attribute mag niet in de toekomst liggen.',
    ],
    'abroad.trips.*.returnDate' => [
        'after_or_equal' => 'Let op: deze datum ligt voor de vertrekdatum.',
    ],
    'circumstances.ppeReplaceFrequency' => [
        'max' => 'Toelichting mag niet langer dan :max karakters zijn.',
    ],
    'test.monsterNumber' => [
        'required_without' => ':Attribute is verplicht als HPZone-nummer niet ingevuld is.',
        'unique' => 'Er bestaat al een case met dit monster nummer',
    ],
    'policyVersion.startDate' => [
        'after_or_equal' => 'Deze datum mag niet in het verleden liggen.',
    ],
];

$customStripped = [];
foreach ($custom as $key => $value) {
    if (strpos($key, '.')) {
        $customStripped[substr($key, strpos($key, '.') + 1)] = $value;
    }
}
$custom = array_merge($custom, $customStripped);

$attributes = [
    /* --[ Global Attributes ]-- */
    'address' => 'adres',
    'amount' => 'bedrag',
    'available' => 'beschikbaar',
    'city' => 'stad',
    'content' => 'inhoud',
    'country' => 'land',
    'currency' => 'valuta',
    'date' => 'datum',
    'date_of_birth' => 'geboortedatum',
    'dateOfLastExposure' => 'laatste contact datum',
    'day' => 'dag',
    'description' => 'omschrijving',
    'duration' => 'tijdsduur',
    'email' => 'e-mailadres',
    'excerpt' => 'uittreksel',
    'firstname' => 'voornaam',
    'gender' => 'geslacht',
    'group' => 'groep',
    'hour' => 'uur',
    'index_age' => 'leeftijd',
    'laboratory' => 'laboratorium',
    'lastname' => 'achternaam',
    'lesson' => 'les',
    'message' => 'bericht',
    'minute' => 'minuut',
    'mobile' => 'mobiel',
    'monsterNumber' => 'monster nummer',
    'month' => 'maand',
    'name' => 'naam',
    'note' => 'notitie',
    'password' => 'wachtwoord',
    'password_confirmation' => 'wachtwoordbevestiging',
    'phone' => 'telefoonnummer',
    'ppeType' => 'type mondkapje',
    'price' => 'prijs',
    'priority' => 'prioriteit',
    'result' => 'testuitslag',
    'second' => 'seconde',
    'sex' => 'geslacht',
    'size' => 'grootte',
    'street' => 'straatnaam',
    'student' => 'student',
    'subject' => 'onderwerp',
    'teacher' => 'docent',
    'time' => 'tijd',
    'title' => 'titel',
    'username' => 'gebruikersnaam',
    'year' => 'jaar',
    'startDate' => 'Startdatum',

    /* --[ Case Fragment Attributes ]-- */

    /* [Case.abroad] */
    'abroad.trips.*.departureDate' => 'datum vertrek',
    'abroad.trips.*.returnDate' => 'datum terugkeer',
    'abroad.wasAbroad' => 'index is in de 14 dagen voor EZD in het buitenland geweest',

    /* [Case.alternateContact] */
    'alternateContact.hasAlternateContact' => 'benader vertegenwoordiger',
    'alternateContact.relationship' => 'relatie tot index',

    /* [Case.alternateLanguage] */
    'alternativeLanguage.useAlternativeLanguage' => 'voorkeurstaal telefonisch contact met GGD',
    'alternativeLanguage.emailLanguage' => 'voorkeurstaal e-mails GGD',
    'alternativeLanguage.hasAlternateResidency' => 'heeft ander verblijfadres',

    /* [Case.alternateResidency] */
    'alternateResidency.remark' => 'opmerkingen',

    /* [Case.call] */
    'call.remarks' => 'opmerkingen',

    /* [Case.communication] */
    'communication.otherAdviceGiven' => 'gegeven andere adviezen',
    'communication.particularities' => 'bijzonderheden',
    'communication.scientificResearchConsent' => 'mag de GGD de index benaderen voor toekomstig onderzoek?',
    'communication.remarksRivm' => 'opmerkingen en bijzonderheden over het BCO-gesprek',

    /* [Case.contact] */
    'contact.phone' => 'telefoon',
    'contact.email' => 'e-mailadres',

    /* [Case.contacts] */
    'contacts.estimatedMissingContacts' => 'hoeveel missende contacten had de index in totaal? (schatting)',
    'contacts.shareNameWithContacts' => 'toestemming naam delen',
    'contacts.estimatedCategory1Contacts' => 'hoeveel categorie 1 contacten had de index in totaal? (schatting)',
    'contacts.estimatedCategory2Contacts' => 'hoeveel categorie 2 contacten had de index in totaal? (schatting)',
    'contacts.estimatedCategory3Contacts' => 'hoeveel categorie 3 contacten had de index in totaal? (schatting)',

    /* [Case.deceased] */
    'deceased.deceasedAt' => 'datum overlijden',
    'deceased.isDeceased' => 'index is overleden',
    'deceased.cause' => 'oorzaak overlijden',

    /* [Case.extensiveContactTracing] */
    'extensiveContactTracing.receivesExtensiveContactTracing' => 'krijgt uitgebreid BCO',
    'extensiveContactTracing.otherDescription' => 'toelichting Andere vorm BCO',

    /* [Case.general] */
    'general.source' => 'afzender van de test',
    'general.reference' => 'referentie',
    'general.organisation' => 'regio',
    'general.organisation.uuid' => 'regio',
    'general.notes' => 'notities',
    'general.hpzoneNumber' => 'HPZone-nummer',

    /* [Case.generalPractioner] */
    'generalPractioner.practiceName' => 'praktijk naam',

    /* [Case.groupTransport] */
    'groupTransport' => 'met gereserveerde stoelen',

    /* [Case.hospital] */
    'hospital.practitionerPhone' => 'telefoonnummer behandelaar',
    'hospital.releasedAt' => 'datum ontslagen',
    'hospital.admittedAt' => 'datum opgenomen',
    'hospital.admittedInICUAt' => 'datum opname intensive care',
    'hospital.isAdmitted' => 'is opgenomen',
    'hospital.reason' => 'reden',
    'hospital.hasGivenPermission' => 'toestemming contact behandelend arts',
    'hospital.isInICU' => 'ligt op IC',
    'hospital.location' => 'locatie',
    'hospital.practitioner' => 'behandelend arts',

    /* [Case.housemates] */
    'housemates.bottlenecks' => 'knelpunten',
    'housemates.hasHouseMates' => 'heeft huisgenoten',

    /* [Case.index] */
    'index.firstname' => 'voornaam',
    'index.lastname' => 'achternaam',
    'index.dateOfBirth' => 'geboortedatum',
    'index.address.postalCode' => 'postcode',
    'index.address.houseNumber' => 'huisnummer',
    'index.address.houseNumberSuffix' => 'huisnummer toevoeging',
    'index.address.street' => 'straat',
    'index.address.town' => 'stad',
    'index.bsn' => 'burgerservicenummer',
    'index.lastThreeDigits' => 'laatste 3 cijfers BSN',
    'index.bsnNotes' => 'identificatie notities',
    'index.bsnCensored' => 'laatste drie cijfers BSN',
    'index.bsnLetters' => 'initialen',
    'index.initials' => 'initialen',

    /* [Case.job] */
    'job.causeForConcernRemark' => 'redenen voor zorgen',
    'job.wasAtJob' => 'heeft gewerkt',
    'job.professionCare' => 'zorgberoep',
    'job.closeContactAtJob' => 'contactberoep',
    'job.professionOther' => 'ander beroep',
    'job.particularities' => 'bijzonderheden',
    'job.otherProfession' => 'anders, namelijk',

    /* [Case.medication] */
    'medication.immunoCompromisedRemarks' => 'immuungecompromitteerd toelichting',
    'medication.hasMedication' => 'gebruikt medicijnen',
    'medication.isImmunoCompromised' => 'heeft verminderde afweer',
    'medication.hasGivenPermission' => 'toestemming contact behandelend arts',
    'medication.practitioner' => 'behandelend arts',
    'medication.hospitalName' => 'naam ziekenhuis',

    /* [Case.pregnancy] */
    'pregnancy.isPregnant' => 'is zwanger',

    /* [Case.riskLocation] */
    'riskLocation.isLivingAtRiskLocation' => 'woont op risicolocatie',
    'riskLocation.type' => 'type risicolocatie',
    'riskLocation.otherType' => 'anders, namelijk',
    'riskLocation.hasRelatedSickness' => 'zijn er gerelateerde ziektegevallen in de instelling?',
    'riskLocation.hasDifferentDiseaseCourse' => 'is het ziekteverloop (mogelijk) afwijkend?',

    /* [Case.symptoms] */
    'symptoms.diseaseCourse' => 'ziekteverloop',
    'symptoms.hasSymptoms' => 'heeft symptomen',

    /* [Case.test] */
    'test.dateOfTest' => 'testdatum',
    'test.dateOfInfectiousnessStart' => 'eerste besmettelijke dag',
    'test.otherReason' => 'andere reden',
    'test.otherInfectionIndicator' => 'andere infectie indicator',
    'test.selfTestLabTestDate' => 'zelftest datum',
    'test.dateOfSymptomOnset' => 'eerste ziektedag (EZD)',
    'test.dateOfResult' => 'datum testuitslag',
    'test.infectionIndicator' => 'infectie indicatie',
    'test.selfTestIndicator' => 'zelftest indicatie',
    'test.labTestIndicator' => 'labtest indicatie',
    'test.selfTestLabTestResult' => 'uitslag labbevestigingstest',
    'test.isReinfection' => 'was eerder besmet',
    'test.previousInfectionDateOfSymptom' => 'EZD eerdere besmetting',
    'test.previousInfectionProven' => 'vorige infectie bewezen',
    'test.previousInfectionReported' => 'eerdere besmetting gemeld',
    'test.otherLabTestIndicator' => 'anders, namelijk',
    'test.monsterNumber' => 'monster nummer',

    /* [Case.underlyingSuffering] */
    'underlyingSuffering.hasUnderlyingSufferingOrMedication' => 'heeft onderliggend lijden of gebruikt medicijnen',
    'underlyingSuffering.hasUnderlyingSuffering' => 'heeft onderliggend lijden',
    'underlyingSuffering.otherItems' => 'andere aandoening',

    /* [Case.vaccination] */
    'vaccination.otherGroup' => 'andere vaccinatie groep',
    'vaccination.otherVaccineType' => 'ander vaccinatietype',
    'vaccination.isVaccinated' => 'is gevaccineerd',
    'vaccination.vaccinationCount' => 'aantal vaccinaties',
    'vaccination.vaccineInjections' => 'vacccinatie',

    /* --[ Context Fragment Attributes ]-- */

    /* [Context.general] */
    'general.remarks' => 'notitie',
    'general.moments.*.day' => 'Datum',
    'general.moments.*.startTime' => 'Van',
    'general.moments.*.endTime' => 'Tot',

    /* [Context.circumstances] */
    'otherCovidMeasures.*' => 'Covid maatregel',
];

$strippedAttributes = [];
$fragmentAttributeReferences = [];

foreach ($attributes as $key => $value) {
    $dotPosition = strpos($key, '.');
    if ($dotPosition === false) {
        continue;
    }

    $strippedAttributes[substr($key, strpos($key, '.') + 1)] = $value;
    $fragmentAttributeReferences[substr_replace($key, '-', $dotPosition, 1)] = $value;
}

$attributes = array_merge($strippedAttributes, $fragmentAttributeReferences, $attributes);

return array_map(
    static fn ($translations) => ValidationMessageFormatter::prefixLabel('Veld', $translations),
    [
        /*
        |--------------------------------------------------------------------------
        | Validation Language Lines
        |--------------------------------------------------------------------------
        |
        | The following language lines contain the default error messages used by
        | the validator class. Some of these rules have multiple versions such
        | as the size rules. Feel free to tweak each of these messages.
        |
        */
        'accepted' => ':Attribute moet geaccepteerd zijn.',
        'active_url' => ':Attribute is geen geldige URL.',
        'after' => ':Attribute moet een datum na :date zijn.',
        'after_or_equal' => ':Attribute moet een datum na of gelijk aan :date zijn.',
        'alpha' => ':Attribute mag alleen letters bevatten.',
        'alpha_dash' => ':Attribute mag alleen letters, nummers, underscores (_) en streepjes (-) bevatten.',
        'alpha_num' => ':Attribute mag alleen letters en nummers bevatten.',
        'array' => ':Attribute moet geselecteerde elementen bevatten.',
        'before' => ':Attribute moet een datum voor :date zijn.',
        'before_or_equal' => ':Attribute moet een datum voor of gelijk aan :date zijn.',
        'between' => [
            'numeric' => ':Attribute moet tussen :min en :max zijn.',
            'file' => ':Attribute moet tussen :min en :max kilobytes zijn.',
            'string' => ':Attribute moet tussen :min en :max karakters zijn.',
            'array' => ':Attribute moet tussen :min en :max items bevatten.',
        ],
        'boolean' => ':Attribute moet ja of nee zijn.',
        'confirmed' => ':Attribute bevestiging komt niet overeen.',
        'date' => ':Attribute moet een datum bevatten.',
        'date_equals' => ':Attribute moet een datum gelijk aan :date zijn.',
        'date_format' => ':Attribute moet een geldig datum formaat bevatten.',
        'different' => ':Attribute en :other moeten verschillend zijn.',
        'digits' => ':Attribute moet bestaan uit :digits cijfers.',
        'digits_between' => ':Attribute moet bestaan uit minimaal :min en maximaal :max cijfers.',
        'dimensions' => ':Attribute heeft geen geldige afmetingen voor afbeeldingen.',
        'distinct' => ':Attribute heeft een dubbele waarde.',
        'email' => ':Attribute is geen geldig e-mailadres.',
        'ends_with' => ':Attribute moet met één van de volgende waarden eindigen: :values.',
        'exists' => ':Attribute bestaat niet.',
        'file' => ':Attribute moet een bestand zijn.',
        'filled' => ':Attribute is verplicht.',
        'gt' => [
            'numeric' => 'De :attribute moet groter zijn dan :value.',
            'file' => 'De :attribute moet groter zijn dan :value kilobytes.',
            'string' => 'De :attribute moet meer dan :value tekens bevatten.',
            'array' => 'De :attribute moet meer dan :value waardes bevatten.',
        ],
        'gte' => [
            'numeric' => 'De :attribute moet groter of gelijk zijn aan :value.',
            'file' => 'De :attribute moet groter of gelijk zijn aan :value kilobytes.',
            'string' => 'De :attribute moet minimaal :value tekens bevatten.',
            'array' => 'De :attribute moet :value waardes of meer bevatten.',
        ],
        'image' => ':Attribute moet een afbeelding zijn.',
        'in' => ':Attribute is ongeldig.',
        'in_array' => ':Attribute bestaat niet in :other.',
        'integer' => ':Attribute moet een getal zijn.',
        'ip' => ':Attribute moet een geldig IP-adres zijn.',
        'ipv4' => ':Attribute moet een geldig IPv4-adres zijn.',
        'ipv6' => ':Attribute moet een geldig IPv6-adres zijn.',
        'json' => ':Attribute moet een geldige JSON-string zijn.',
        'lt' => [
            'numeric' => 'De :attribute moet kleiner zijn dan :value.',
            'file' => 'De :attribute moet kleiner zijn dan :value kilobytes.',
            'string' => 'De :attribute moet minder dan :value tekens bevatten.',
            'array' => 'De :attribute moet minder dan :value waardes bevatten.',
        ],
        'lte' => [
            'numeric' => 'De :attribute moet kleiner of gelijk zijn aan :value.',
            'file' => 'De :attribute moet kleiner of gelijk zijn aan :value kilobytes.',
            'string' => 'De :attribute moet maximaal :value tekens bevatten.',
            'array' => 'De :attribute moet :value waardes of minder bevatten.',
        ],
        'max' => [
            'numeric' => ':Attribute mag niet hoger dan :max zijn.',
            'file' => ':Attribute mag niet meer dan :max kilobytes zijn.',
            'string' => ':Attribute mag niet uit meer dan :max tekens bestaan.',
            'array' => ':Attribute mag niet meer dan :max items bevatten.',
        ],
        'mimes' => ':Attribute moet een bestand zijn van het bestandstype :values.',
        'mimetypes' => ':Attribute moet een bestand zijn van het bestandstype :values.',
        'min' => [
            'numeric' => ':Attribute moet minimaal :min zijn.',
            'file' => ':Attribute moet minimaal :min kilobytes zijn.',
            'string' => ':Attribute moet minimaal :min tekens zijn.',
            'array' => ':Attribute moet minimaal :min items bevatten.',
        ],
        'multiple_of' => ':Attribute moet een veelvoud van :value zijn.',
        'not_in' => 'Het formaat van :attribute is ongeldig.',
        'not_regex' => 'De :attribute formaat is ongeldig.',
        'numeric' => ':Attribute moet een nummer zijn.',
        'password' => 'Wachtwoord is onjuist.',
        'present' => ':Attribute moet bestaan.',
        'regex' => ':Attribute formaat is ongeldig.',
        'required' => ':Attribute is verplicht.',
        'required_if' => ':Attribute is verplicht indien :other gelijk is aan :value.',
        'required_unless' => ':Attribute is verplicht tenzij :other gelijk is aan :values.',
        'required_with' => ':Attribute is verplicht i.c.m. :values',
        'required_with_all' => ':Attribute is verplicht i.c.m. :values',
        'required_without' => ':Attribute is verplicht als :values niet ingevuld is.',
        'required_without_all' => ':Attribute is verplicht als :values niet ingevuld zijn.',
        'prohibited_if' => ':attribute kan niet worden ingevuld wanneer :other is :value.',
        'same' => ':Attribute en :other moeten overeenkomen.',
        'size' => [
            'numeric' => ':Attribute moet :size zijn.',
            'file' => ':Attribute moet :size kilobyte zijn.',
            'string' => ':Attribute moet :size tekens zijn.',
            'array' => ':Attribute moet :size items bevatten.',
        ],
        'starts_with' => ':Attribute moet starten met een van de volgende: :values.',
        'string' => ':Attribute moet een tekst zijn.',
        'timezone' => ':Attribute moet een geldige tijdzone zijn.',
        'unique' => ':Attribute is al in gebruik.',
        'uploaded' => 'Het uploaden van :attribute is mislukt.',
        'url' => ':Attribute moet een geldig URL zijn.',
        'uuid' => ':Attribute moet een geldig UUID zijn.',
        'phone' => ':Attribute moet een geldig telefoonnummer zijn.',
        'postal_code' => 'Dit is geen geldige postcode.',

        /*
        |--------------------------------------------------------------------------
        | Custom Validation Language Lines
        |--------------------------------------------------------------------------
        |
        | Here you may specify custom validation messages for attributes using the
        | convention "attribute.rule" to name the lines. This makes it quick to
        | specify a specific custom language line for a given attribute rule.
        |
        */
        'custom' => $custom,
        /*
        |--------------------------------------------------------------------------
        | Custom Validation Attributes
        |--------------------------------------------------------------------------
        |
        | The following language lines are used to swap attribute place-holders
        | with something more reader friendly such as E-Mail Address instead
        | of "email". This simply helps us make messages a little cleaner.
        |
        */
        'attributes' => $attributes,

        'values' => [
            'admittedAt' => [
                'today' => 'vandaag',
            ],
            'admittedInICUAt' => [
                'today' => 'vandaag',
            ],
            'dateOfBirth' => [
                'today' => 'vandaag',
            ],
            'dateOfTest' => [
                'today' => 'vandaag',
            ],
            'dateOfInfectiousnessStart' => [
                'today' => 'vandaag',
            ],
            'deceasedAt' => [
                'today' => 'vandaag',
            ],
            'index' => [
                'dateOfBirth' => [
                    'today' => 'vandaag',
                ],
            ],
            'releasedAt' => [
                'today' => 'vandaag',
            ],
            'test' => [
                'dateOfTest' => [
                    'today' => 'vandaag',
                ],
            ],
        ],

        'This person was under the age of 45.' => 'Deze persoon was jonger dan 45 jaar.',
        'This person is under the age of 18.' => 'Deze persoon is jonger dan 18 jaar.',
        'This person is under the age of 4.' => 'Deze persoon is jonger dan 4 jaar.',
        'This person is under the age of 6 months.' => 'Deze persoon was jonger dan 6 maanden.',
        'Deceased date should not be in the future.' => 'Datum overlijden mag niet in de toekomst liggen.',

        'Date of lab result is more than 21 days after date of symptom onset.' => 'Datum labuitslag ligt meer dan 21 dagen na de eerste ziektedag.',
        'Date symptom onset is more than 21 days before lab result.' => 'Eerste ziektedag ligt meer dan 21 dagen voor datum labuitslag.',
        'Date of symptom onset should not be after deceased date.' => 'Eerste ziektedag mag niet na de datum van overlijden liggen.',
        'Symptom onset should be after previous infection.' => 'Eerste ziektedag mag niet eerder zijn dan de vorige infectie periode.',
        'Previous infection date should be before symptom onset.' => 'De vorige infectie datum mag niet na de huidige eerste ziektedag liggen.',
        'Date of symptom onset is after case creation date.' => 'De eerste ziektedag is na de melding bij de GGD.',
        'Date of symptom onset is within 8 weeks of previous infection.' => 'De eerste ziektedag is binnen 8 weken van de vorige infectie periode.',
        'Confirmation of selfTest in lab is before date of symptom onset.' => 'Bevestiging van de zelftest in het lab ligt voor de eerste ziektedag.',
        'This person was a Care Professional.' => 'Deze persoon was een zorgmedewerker.',
        'Underlying suffering should be asked for.' => 'Onderliggend lijden dient uitgevraagd te worden.',
        'This care professional is under the age of 16 years.' => 'Deze zorg professional is jonger dan 16 jaar',
        'This care professional is older than 65 years.' => 'Deze zorg professional is ouder dan 65 jaar',
        'Date of birth should not be after admission at hospital.' => 'De geboortedatum mag niet na de datum opname in het ziekenhuis liggen.',
        'Hospital admission should not be before date of birth.' => 'De datum opname in het ziekenhuis mag niet eerder zijn dan de geboortedatum.',
        'Hospital admission should not be after start of surveillance.' => 'De datum opname in het ziekenhuis mag niet eerder zijn dan start van de monitoring periode.',
        'Date of birth may not be on case creation date or after.' => 'De geboortedatum mag niet op of na de meldingsdatum liggen.',
        'Date of symptom onset should be after date of birth.' => 'Eerste ziektedag kan niet voor de geboortedatum liggen.',
        'Date symptom onset should not be after hospital admission.' => 'Eerste ziektedag kan niet na opname ziekenhuis liggen.',
        'Date symptom onset should not be after admission at ICU.' => 'Eerste ziektedag kan niet na opname IC liggen.',
        'uniquestartdate' => 'Op deze datum wordt al een andere beleidsversie geactiveerd',
    ],
);
