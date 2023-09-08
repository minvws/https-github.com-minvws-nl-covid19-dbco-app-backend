<?php

declare(strict_types=1);

return [
    'confirmation' => [
        'subject' => 'Persoonlijke adviezen op basis van je vragenlijst',
        'title' => 'Beste :name,',
        'intro' => 'Bedankt voor het invullen van de vragenlijst op www.zelfbco.nl. Je krijgt deze mail, omdat je hebt aangegeven dat je de adviezen per e-mail wilt ontvangen. Hieronder kun je alles nog eens nalezen.'
        ,
        'advices' => [
            'title' => 'Wat kun je nu doen?',
            'staff' => [
                'title' => 'Advies van medewerker gaat voor',
                'content' => 'Word je door de GGD gebeld over jouw besmetting? Volg dan het advies dat je aan de telefoon krijgt.',
            ],
            'stayHome' => [
                'title' => 'Blijf thuis',
                'content' => 'Je kunt corona gemakkelijk doorgeven aan anderen. Blijf daarom thuis. Je mag weer naar buiten als je op :contagiousPeriodEndDate minimaal 24 uur geen klachten meer hebt. Heb je op :contagiousPeriodEndDate nog wel klachten? Blijf dan thuis tot je minimaal 24 uur geen klachten meer hebt.',
                'link' => [
                    'text' => 'Lees hier alle leefregels voor jou',
                    'href' => 'https://lci.rivm.nl/covid-19-thuis',
                ],
            ],
            'maybeImmunoComporomised' => [
                'title' => 'Misschien langer thuisblijven',
                'content' => 'Je gaf aan dat je een chronische ziekte hebt waardoor je extra op je gezondheid moet letten en mogelijk medicijnen gebruikt. In jouw geval ben je misschien ook langer besmettelijk voor anderen. Neem contact op met je GGD voor een persoonlijk advies.',
            ],
            'hasHouseMates' => [
                'title' => 'Voorkom contact met huisgenoten',
                'content' => 'Heb je huisgenoten? Dan is het belangrijk om contact met hen te vermijden.',
                'link' => [
                    'text' => 'Lees wat je je huisgenoten kan vertellen',
                    'href' => 'https://lci.rivm.nl/covid-19-huisgenoten',
                ],
            ],
            'hasSymptoms' => [
                'title' => 'Waarschuw je contacten',
                'content' => 'Je kunt andere mensen besmetten vanaf twee dagen voordat je klachten begonnen. Waarschuw mensen met wie je contact hebt gehad tussen :contagiousPeriodStartDate en vandaag.',
                'link' => [
                    'text' => 'Lees wat je je contacten kan vertellen',
                    'href' => 'https://lci.rivm.nl/covid-19-nauwe-contacten',
                ],
            ],
            'hasNoSymptoms' => [
                'title' => 'Waarschuw je contacten',
                'content' => 'Je kunt anderen besmetten, ook als je zelf geen klachten hebt. Het is dan lastig in te schatten vanaf wanneer je besmettelijk bent. We rekenen met 2 dagen voor de testdatum. Waarschuw daarom mensen met wie je contact hebt gehad tussen :contagiousPeriodStartDate en vandaag.',
                'link' => [
                    'text' => 'Lees wat je je contacten kan vertellen',
                    'href' => 'https://lci.rivm.nl/covid-19-nauwe-contacten',
                ],
            ],
            'medical' => [
                'title' => 'Medische hulp',
                'content' => 'Heb je ernstige klachten zoals hoge koorts of moeite met ademhalen? Of zit je in een risicogroep en krijg je koorts? Bel dan je huisarts.',
            ],
        ],
        'additionalAdvices' => [
            'title' => 'Extra adviezen voor jou',
            'items' => [
                'isSelfTestFlow' => [
                    'text' => 'Plan nu een test bij de GGD',
                    'href' => 'https://coronatest.nl/',
                ],
                'isPregnantOrRecentBirth' => [
                    'text' => 'Zwangerschap en COVID-19',
                    'href' => 'https://www.rivm.nl/coronavirus-covid-19/zwangerschap',
                ],
                'probablyHasHouseMates' => [
                    'text' => 'Kinderen, school en COVID-19',
                    'href' => 'https://www.rijksoverheid.nl/onderwerpen/coronavirus-covid-19/thuisquarantaine/quarantaine-gezinnen-met-kinderen',
                ],
                'isJobSectorCare' => [
                    'text' => 'Informatie voor zorgprofessionals',
                    'href' => 'https://www.rivm.nl/coronavirus-covid-19/professionals',
                ],
            ],
        ],
        'coronaMelderBanner' => [
            'title' => 'Heb je CoronaMelder?',
            'text' => 'Waarschuw dan de mensen bij wie je in de buurt bent geweest. Zo voorkom je dat zij het virus verder doorgeven.',
        ],
        'questions' => [
            'title' => 'Vragen?',
            'text' => 'Het liefst belt de GGD iedereen die besmet is met corona voor het bron-en contactonderzoek. Als er veel besmettingen zijn, lukt dat niet altijd. Ben je niet gebeld en heb je wel vragen? Je kunt altijd contact opnemen met de GGD in je regio.',
        ],
        'outro' => [
            'text' => 'We wensen je sterkte en beterschap toe.',
        ],
        'signature' => [
            'text' => 'Met vriendelijke groet,',
            'name' => 'De GGD',
        ],
        'footer' => [
            'text' => 'Dit bericht is automatisch verzonden. Je kan er daarom niet op reageren.',
        ],
    ],
];
