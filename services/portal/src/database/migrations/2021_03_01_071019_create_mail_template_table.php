<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

class CreateMailTemplateTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mail_template', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('type');
            $table->char('language', 2);
            $table->string('subject');
            $table->longText('body');
            $table->text('summary')->nullable();
            $table->text('preview');
            $table->text('footer');

            $table->index(['type', 'language']);
        });

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'missed_phone',
            'language' => 'nl',
            'subject' => 'Belpoging van uw GGD betreft COVID-19',
            'body' => $this->missedPhoneBodyNL(),
            'preview' => ' ',
            'summary' => ' ',
            'footer' => $this->footerNL(false),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'rules',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->rulesBodyNL(),
            'preview' => $this->previewIndexNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_1',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->contactInfectionClass1NL(),
            'preview' => $this->previewContactNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_2a',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->contactInfectionClass2a2bNL(),
            'preview' => $this->previewContactNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_2b',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->contactInfectionClass2a2bNL(),
            'preview' => $this->previewContactNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_3a',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->contactInfectionClass3a3bNL(),
            'preview' => $this->previewContactNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_3b',
            'language' => 'nl',
            'subject' => 'Informatiebrief over COVID-19 van uw GGD',
            'body' => $this->contactInfectionClass3a3bNL(),
            'preview' => $this->previewContactNL(),
            'summary' => ' ',
            'footer' => $this->footerNL(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'missed_phone',
            'language' => 'en',
            'subject' => 'The local Public Health Service (GGD) has tried to contact you concerning COVID-19',
            'body' => $this->missedPhoneBodyEN(),
            'preview' => ' ',
            'summary' => ' ',
            'footer' => $this->footerEN(false),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'rules',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->rulesBodyEN(),
            'preview' => $this->previewIndexEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_1',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->contactInfectionClass1EN(),
            'preview' => $this->previewContactEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_2a',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->contactInfectionClass2a2bEN(),
            'preview' => $this->previewContactEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_2b',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->contactInfectionClass2a2bEN(),
            'preview' => $this->previewContactEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_3a',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->contactInfectionClass3a3bEN(),
            'preview' => $this->previewContactEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);

        DB::table('mail_template')->insert([
            'uuid' => Uuid::uuid4()->toString(),
            'type' => 'contact_infection_3b',
            'language' => 'en',
            'subject' => 'Information from your local Public Health Service concerning COVID-19',
            'body' => $this->contactInfectionClass3a3bEN(),
            'preview' => $this->previewContactEN(),
            'summary' => ' ',
            'footer' => $this->footerEN(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_template');
    }

    private function previewIndexNL(): string
    {
        return <<<'HTML'
{{ggdRegion}} stuurt je dit bericht in verband met een besmetting met COVID-19. In het bericht staan de belangrijkste leefregels die gelden voor een besmet persoon. Log in met DigID om verder te lezen.
HTML;
    }

    private function previewIndexEN(): string
    {
        return <<<'HTML'
{{ggdRegion}} sends you this message in response to a COVID-19 infection. The message contains the most important behavioural guidelines that apply to the infected person. Log in with DigID to continue reading.
HTML;
    }

    private function previewContactNL(): string
    {
        return <<<'HTML'
Je bent in de buurt geweest van iemand met corona. Hierdoor ben je zelf misschien ook besmet geraakt. {{ggdRegion}} wil daarom de komende twee weken je gezondheid in de gaten houden. Blijf vanaf nu thuis om te voorkomen dat je anderen besmet. Log in met DigID om te zien wanneer de ontmoeting met de besmette persoon was en wanneer je je het beste kunt laten testen.
HTML;
    }

    private function previewContactEN(): string
    {
        return <<<'HTML'
You have been around someone with corona. As a result, you may have become infected yourself. {{ggdRegion}} would therefore like to monitor your health for the next two weeks. Stay home from now on to avoid infecting others. Log in with DigID to see when you met the infected person and when it's best to get tested.
HTML;
    }

    private function footerNL(bool $withDossierNumber = true): string
    {
        $footer = <<<'HTML'
**Team Infectieziektenbestrijding** - {{ggdRegion}}

Heb je vragen over dit bericht?
Bel ons via {{ggdPhoneNumber}}
HTML;
        if ($withDossierNumber) {
            $footer .= <<<'HTML'

Je casenummer: {{caseNumber}}
HTML;
        }

        return $footer;
    }

    private function footerEN(bool $withDossierNumber = true): string
    {
        $footer = <<<'HTML'
**Team Infectious Disease Control** - {{ggdRegion}}

Any questions about this message?
Call us at {{ggdPhoneNumber}}
HTML;
        if ($withDossierNumber) {
            $footer .= <<<'HTML'

Your case number: {{caseNumber}}
HTML;
        }

        return $footer;
    }

    private function missedPhoneBodyNL(): string
    {
        return <<<'HTML'
Beste {{fullname}},


GGD {{ggdRegion}} heeft vandaag enkele keren geprobeerd u telefonisch te bereiken op {{phoneNumber}}. Dit is helaas niet gelukt. We bellen vandaag en/of morgen nog een keer. Wilt u de telefoon bij de hand houden zodat u op tijd kunt opnemen? Alvast bedankt.


Met vriendelijke groet,
HTML;
    }

    private function rulesBodyNL(): string
    {
        return <<<'HTML'
Beste {{fullname}},

Hierbij ontvangt u de links naar de online brieven van het RIVM met daarin de adviezen en leefregels. U kunt op deze links klikken.

**Wilt u deze brieven goed doorlezen?**

- In de eerste brief staat informatie voor degene die positief is getest voor COVID-19:

  https://lci.rivm.nl/informatiepatientthuis

  Eenvoudig met plaatjes:

  https://lci.rivm.nl/covid-19-patient

- De tweede brief is voor gezins-/huisgenoten:

  https://lci.rivm.nl/informatiebriefhuisgenootthuis

  Eenvoudig met plaatjes voor huisgenoten:

  https://lci.rivm.nl/covid-19-huisgenoten

**U ontvangt los van deze e-mail ook een e-mail voor uw overige niet-nauwe contacten. Wilt u deze e-mail doorsturen naar deze contacten, zoals u heeft besproken met de GGD-medewerker?**


In de bijlage vindt u de quarantainegids en een uitnodiging voor een medisch wetenschappelijk onderzoek. Dit onderzoek is alleen voor mensen van 50 jaar en ouder.
Mocht u nog vragen hebben, neem dan gerust contact met ons op. Vermeld hierbij altijd uw dossiernummer: {{caseNumber}}

Ik hoop u zo voldoende te hebben geïnformeerd.


Met vriendelijke groet,
HTML;
    }

    private function contactInfectionClass1NL(): string
    {
        return <<<'HTML'
Beste {{fullname}},

U ontvangt deze brief als huisgenoot van iemand die positief is getest op COVID-19.
Via de link kunt u de brief van het RIVM lezen. Hierin staat informatie over de leefregels die u moet volgen om te voorkomen dat u anderen besmet.

**Wilt u deze brieven goed doorlezen?**

- Klik op de link om de brief te openen:

  https://lci.rivm.nl/informatiebriefhuisgenootthuis

- Eenvoudig met plaatjes:

  https://lci.rivm.nl/covid-19-huisgenoten

**De GGD-medewerker heeft met u een testadvies besproken. Maak een testafspraak via 0800-2035.**
Adviesdatum test 1: zo spoedig mogelijk, om te kijken of u nu besmet bent met COVID-19.
Adviesdatum test 2: op of na dag 5 na het laatste contact met uw besmettelijke huisgenoot.

Vermeld bij contact met de GGD altijd het volgende dossiernummer: {{caseNumber}}

In de bijlage vindt u de quarantainegids, met informatie en tips over thuisquarantaine.

Ik hoop u zo voldoende te hebben geïnformeerd.


Met vriendelijke groet,
HTML;
    }

    private function contactInfectionClass2a2bNL(): string
    {
        return <<<'HTML'
Beste {{fullname}},

U ontvangt deze brief omdat u contact heeft gehad met iemand die positief is getest op COVID-19.
Via de link kunt u de brief van het RIVM lezen. Hierin staat informatie over de leefregels die u moet volgen om te voorkomen dat u anderen besmet.

**Wilt u deze brieven goed doorlezen?**

- Klik op de link om de brief te openen:

  https://lci.rivm.nl/informatie-nauwe-contacten-patient

- Eenvoudig met plaatjes:

  https://lci.rivm.nl/covid-19-nauwe-contacten

**De GGD-medewerker heeft met u een testadvies besproken. Maak een testafspraak via 0800-2035.**
Adviesdatum test 1: zo spoedig mogelijk, om te kijken of u nu besmet bent met COVID-19.
Adviesdatum test 2: {{dateTestDay5}}

Vermeld bij contact met de GGD altijd het volgende dossiernummer: {{caseNumber}}

In de bijlage vindt u de quarantainegids, met informatie en tips over thuisquarantaine.

Ik hoop u zo voldoende te hebben geïnformeerd.


Met vriendelijke groet,
HTML;
    }

    private function contactInfectionClass3a3bNL(): string
    {
        return <<<'HTML'
Beste {{fullname}},

**[Zou u zo vriendelijk willen zijn om deze mail door te sturen aan uw overige niet-nauwe contacten?]**

Beste lezer, U ontvangt deze brief omdat u contact heeft gehad met iemand die positief is getest op COVID-19. Wij hebben gevraagd deze mail met de link naar de informatiebrief van het RIVM aan u door te sturen. Hier staan de leefregels die u moet volgen om te voorkomen dat u anderen besmet.

- Klik op deze link om de brief te openen:

  https://lci.rivm.nl/informatie-contacten-patient

  Wilt u deze brief goed doorlezen?

**De GGD adviseert u op korte termijn een testafspraak te maken**, omdat u besmet kunt zijn met COVID-19. Maak deze testafspraak via 0800-2035 en noem hierbij het onderstaande dossiernummer. U hoeft tijdens het wachten op de testafspraak en de uitslag **niet** thuis in quarantaine te gaan.


Vermeld bij contact met de GGD altijd het volgende dossiernummer: {{caseNumber}}

Ik hoop u zo voldoende te hebben geïnformeerd.


Met vriendelijke groet,
HTML;
    }

    private function missedPhoneBodyEN(): string
    {
        return <<<'HTML'
Dear {{fullname}},


The {{ggdRegion}} Municipal Public Health Service has been unable to reach you by phone on {{phoneNumber}}. We will try again later today or tomorrow. Please keep your phone nearby.


Best regards,
HTML;
    }

    private function rulesBodyEN(): string
    {
        return <<<'HTML'
Dear {{fullname}},


We recently spoke on the phone with regard to COVID-19. Below, you find two links to two information letters, containing advices and rules from the Dutch National Institute for Public Health and the Environment (RIVM). **Please read these letters carefully.**

- The first letter contains information for those who have tested positive for COVID-19:

  https://lci.rivm.nl/covid-19-patient-EN

- The second letter is addressed to the household members:

  https://lci.rivm.nl/covid-19-huisgenoten-EN

**You will also receive a separate email for your remaining contacts (other non-close contacts).**
Would you be so kind as to forward this email to your other non-close contacts?

Case number for future reference: {{caseNumber}}

Please contact us if you have any questions.


Best regards,
HTML;
    }

    private function contactInfectionClass1EN(): string
    {
        return <<<'HTML'
Dear {{fullname}},


You are receiving this email because you are the household member of someone who has tested positive for COVID-19. Below you find the link to the website which explains what you should do during the 10 days after your last contact moment with this person.

- **Please read the information on our website carefully:**

  https://lci.rivm.nl/covid-19-huisgenoten-EN

If you develop any symptoms of COVID-19, call the GDD on {{ggdRegion}} for scheduling a test as soon as possible.

Case number for future reference: {{caseNumber}}

Please contact us if you have any questions.


Best regards,
HTML;
    }

    private function contactInfectionClass2a2bEN(): string
    {
        return <<<'HTML'
Dear {{fullname}},


You are receiving this email because you have been in close contact with someone who has tested positive for COVID-19. Below you find the link to the website which explains what you should do during the 10 days after your last contact moment with this person.

- **Please read the information on our website carefully:**

  https://lci.rivm.nl/covid-19-nauwe-contacten-EN

If you develop any symptoms of COVID-19, call the GDD on {{ggdRegion}} for scheduling a test as soon as possible.

Case number for future reference: {{caseNumber}}

Please contact us if you have any questions.


Best regards,
HTML;
    }

    private function contactInfectionClass3a3bEN(): string
    {
        return <<<'HTML'
**[Would you be so kind as to forward this email to your remaining contacts (other non-close contacts)]**

Dear reader,


You are receiving this email because you have been in contact with someone who has tested positive for COVID-19. We have asked this person to send this information to you.

- **Please read the information on our website carefully:**

  https://lci.rivm.nl/covid-19-overige-contacten-EN

It is important that you follow these rules and watch your health carefully for 10 days. This way, we can prevent the spread of COVID-19.

**The Public Health Service GGD advises you to make a test appointment on short notice**, to check if you are possibly infected with COVID-19. Please schedule a COVD-19 test by calling 0800-2035 and provide the case number below. While awaiting the test appointment or the test result, you do **not** need to go into quarantine.

Case number for future reference: {{caseNumber}}

Please contact us if you have any questions.


Best regards,
HTML;
    }
}
