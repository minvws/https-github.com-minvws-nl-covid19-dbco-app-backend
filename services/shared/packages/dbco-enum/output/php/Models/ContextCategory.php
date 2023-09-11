<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 * Context categories.
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategory.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ContextCategory restaurant() restaurant() Restaurant / Café
 * @method static ContextCategory club() club() Club / Disco
 * @method static ContextCategory accomodatieBinnenland() accomodatieBinnenland() Accommodatie binnenland
 * @method static ContextCategory retail() retail() Retail, detailhandel
 * @method static ContextCategory evenementVast() evenementVast() Evenement / Attractie met vaste zitplekken
 * @method static ContextCategory evenementZonder() evenementZonder() Evenement / Attractie zonder vaste zitplekken
 * @method static ContextCategory zwembad() zwembad() Zwembad / Sauna
 * @method static ContextCategory horecaOverig() horecaOverig() Overige horeca / Retail / Entertainment
 * @method static ContextCategory asielzoekerscentrum() asielzoekerscentrum() Asielzoekerscentrum
 * @method static ContextCategory penitentiaireInstelling() penitentiaireInstelling() Penitentiaire instelling
 * @method static ContextCategory residentieleJeugdinstelling() residentieleJeugdinstelling() (Semi-)residentiele jeugdinstelling
 * @method static ContextCategory opvangOverig() opvangOverig() Overige maatschappelijke opvang
 * @method static ContextCategory kinderOpvang() kinderOpvang() Kinderdagverblijf / Kinderopvang / BSO
 * @method static ContextCategory basisOnderwijs() basisOnderwijs() Basisonderwijs
 * @method static ContextCategory voortgezetOnderwijs() voortgezetOnderwijs() Voortgezet Onderwijs
 * @method static ContextCategory mbo() mbo() MBO
 * @method static ContextCategory hboUniversiteit() hboUniversiteit() HBO of WO (Universiteit)
 * @method static ContextCategory buitenland() buitenland() Buitenland reis
 * @method static ContextCategory zeeTransport() zeeTransport() Schip / Zee- en binnenvaart / Haven
 * @method static ContextCategory vliegTransport() vliegTransport() Vliegreis / Luchthaven
 * @method static ContextCategory transportOverige() transportOverige() Overig transport
 * @method static ContextCategory thuis() thuis() Thuissituatie
 * @method static ContextCategory bezoek() bezoek() Bezoek in de thuissituatie
 * @method static ContextCategory groep() groep() Studentenhuis
 * @method static ContextCategory feest() feest() Feest / Groepsbijeenkomst privésfeer
 * @method static ContextCategory bruiloft() bruiloft() Bruiloft
 * @method static ContextCategory uitvaart() uitvaart() Uitvaart
 * @method static ContextCategory religie() religie() Religieuze bijeenkomst
 * @method static ContextCategory koor() koor() Koor
 * @method static ContextCategory studentenverening() studentenverening() Studentenverening-activiteiten
 * @method static ContextCategory sport() sport() Sportclub/ -school
 * @method static ContextCategory verenigingOverige() verenigingOverige() Overige verenigingen
 * @method static ContextCategory verpleeghuis() verpleeghuis() Verpleeghuis of woonzorgcentrum
 * @method static ContextCategory instelling() instelling() Instelling voor verstandelijk en/of lichamelijk beperkten
 * @method static ContextCategory ggzInstelling() ggzInstelling() GGZ-instelling (geestelijke gezondheidszorg)
 * @method static ContextCategory begeleid() begeleid() Begeleid kleinschalig wonen
 * @method static ContextCategory dagopvang() dagopvang() Dagopvang
 * @method static ContextCategory thuiszorg() thuiszorg() Thuiszorg
 * @method static ContextCategory ziekenhuis() ziekenhuis() Ziekenhuis
 * @method static ContextCategory huisarts() huisarts() Huisartsen praktijk
 * @method static ContextCategory zorgOverig() zorgOverig() Overige gezondheidzorg
 * @method static ContextCategory omgevingBuiten() omgevingBuiten() Omgeving (buiten)
 * @method static ContextCategory waterBuiten() waterBuiten() Water (buiten)
 * @method static ContextCategory dieren() dieren() Dieren
 * @method static ContextCategory overig() overig() Overig
 * @method static ContextCategory vleesverwerkingSlachthuis() vleesverwerkingSlachthuis() Vleesverwerking / Slachthuis
 * @method static ContextCategory landEnTuinbouw() landEnTuinbouw() Land- en tuinbouw
 * @method static ContextCategory bouw() bouw() Bouw
 * @method static ContextCategory fabriek() fabriek() Fabriek
 * @method static ContextCategory kantoorOverigeBranche() kantoorOverigeBranche() Kantoor overige branche
 * @method static ContextCategory overigeAndereWerkplek() overigeAndereWerkplek() Overige andere werkplek
 * @method static ContextCategory onbekend() onbekend() Onbekend

 * @property-read string $value
 * @property-read ContextCategoryGroup $group Category group
 * @property-read string $suggestionGroup Suggestion group
*/
final class ContextCategory extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ContextCategory',
           'tsConst' => 'contextCategory',
           'description' => 'Context categories.',
           'properties' =>
          (object) array(
             'description' =>
            (object) array(
               'type' => 'string',
               'description' => 'Description',
               'scope' => 'ts',
               'phpType' => 'string',
            ),
             'group' =>
            (object) array(
               'type' => 'ContextCategoryGroup',
               'description' => 'Category group',
               'scope' => 'shared',
               'phpType' => 'ContextCategoryGroup',
            ),
             'suggestionGroup' =>
            (object) array(
               'type' => 'string',
               'description' => 'Suggestion group',
               'scope' => 'shared',
               'phpType' => 'string',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'value' => 'restaurant',
               'label' => 'Restaurant / Café',
               'description' => 'O.a. lunchroom, kroeg',
               'group' => 'horeca',
               'suggestionGroup' => 'horeca',
               'name' => 'restaurant',
            ),
            1 =>
            (object) array(
               'value' => 'club',
               'label' => 'Club / Disco',
               'description' => null,
               'group' => 'horeca',
               'suggestionGroup' => 'horeca',
               'name' => 'club',
            ),
            2 =>
            (object) array(
               'value' => 'accomodatie_binnenland',
               'label' => 'Accommodatie binnenland',
               'description' => 'O.a. hotel, B&B, camping, vakantiepark of -huis',
               'group' => 'horeca',
               'suggestionGroup' => 'accommodatie',
               'name' => 'accomodatieBinnenland',
            ),
            3 =>
            (object) array(
               'value' => 'retail',
               'label' => 'Retail, detailhandel',
               'description' => 'O.a. winkel, supermarkt, tuin- of bouwcentrum, woonboulevard',
               'group' => 'horeca',
               'suggestionGroup' => 'retail',
               'name' => 'retail',
            ),
            4 =>
            (object) array(
               'value' => 'evenement_vast',
               'label' => 'Evenement / Attractie met vaste zitplekken',
               'description' => 'O.a. theater, bioscoop, sportwedstrijd',
               'group' => 'horeca',
               'suggestionGroup' => 'evenement',
               'name' => 'evenementVast',
            ),
            5 =>
            (object) array(
               'value' => 'evenement_zonder',
               'label' => 'Evenement / Attractie zonder vaste zitplekken',
               'description' => 'O.a. pretpark, museum, festival, dierentuin, beurs, demonstratie',
               'group' => 'horeca',
               'suggestionGroup' => 'evenement',
               'name' => 'evenementZonder',
            ),
            6 =>
            (object) array(
               'value' => 'zwembad',
               'label' => 'Zwembad / Sauna',
               'description' => null,
               'group' => 'horeca',
               'suggestionGroup' => 'zwembad',
               'name' => 'zwembad',
            ),
            7 =>
            (object) array(
               'value' => 'horeca_overig',
               'label' => 'Overige horeca / Retail / Entertainment',
               'description' => 'O.a. kapper, beautysalon, massagesalon',
               'group' => 'horeca',
               'suggestionGroup' => 'horeca_overig',
               'name' => 'horecaOverig',
            ),
            8 =>
            (object) array(
               'value' => 'asielzoekerscentrum',
               'label' => 'Asielzoekerscentrum',
               'description' => 'O.a. aanmeld- en opvangcentrum AZC',
               'group' => 'opvang',
               'suggestionGroup' => 'maatschappelijke_opvang',
               'name' => 'asielzoekerscentrum',
            ),
            9 =>
            (object) array(
               'value' => 'penitentiaire_instelling',
               'label' => 'Penitentiaire instelling',
               'description' => 'O.a. huis van bewaring, tbs-kliniek, semi-residentieel volwassen',
               'group' => 'opvang',
               'suggestionGroup' => 'maatschappelijke_opvang',
               'name' => 'penitentiaireInstelling',
            ),
            10 =>
            (object) array(
               'value' => 'residentiele_jeugdinstelling',
               'label' => '(Semi-)residentiele jeugdinstelling',
               'description' => null,
               'group' => 'opvang',
               'suggestionGroup' => 'maatschappelijke_opvang',
               'name' => 'residentieleJeugdinstelling',
            ),
            11 =>
            (object) array(
               'value' => 'opvang_overig',
               'label' => 'Overige maatschappelijke opvang',
               'description' => 'O.a. thuis- en dakloos, crisis, blijf-van-mijn-lijf, verslaving',
               'group' => 'opvang',
               'suggestionGroup' => 'maatschappelijke_opvang',
               'name' => 'opvangOverig',
            ),
            12 =>
            (object) array(
               'value' => 'kinder_opvang',
               'label' => 'Kinderdagverblijf / Kinderopvang / BSO',
               'description' => 'Ook medisch kinderdagverblijf, peuterspeelzaal',
               'group' => 'onderwijs',
               'suggestionGroup' => 'kinderopvang',
               'name' => 'kinderOpvang',
            ),
            13 =>
            (object) array(
               'value' => 'basis_onderwijs',
               'label' => 'Basisonderwijs',
               'description' => 'Ook speciaal basisonderwijs',
               'group' => 'onderwijs',
               'suggestionGroup' => 'onderwijs',
               'name' => 'basisOnderwijs',
            ),
            14 =>
            (object) array(
               'value' => 'voortgezet_onderwijs',
               'label' => 'Voortgezet Onderwijs',
               'description' => 'Ook speciaal voortgezet onderwijs, praktijkschool',
               'group' => 'onderwijs',
               'suggestionGroup' => 'onderwijs',
               'name' => 'voortgezetOnderwijs',
            ),
            15 =>
            (object) array(
               'value' => 'mbo',
               'label' => 'MBO',
               'description' => null,
               'group' => 'onderwijs',
               'suggestionGroup' => 'onderwijs',
               'name' => 'mbo',
            ),
            16 =>
            (object) array(
               'value' => 'hbo_universiteit',
               'label' => 'HBO of WO (Universiteit)',
               'description' => null,
               'group' => 'onderwijs',
               'suggestionGroup' => 'onderwijs',
               'name' => 'hboUniversiteit',
            ),
            17 =>
            (object) array(
               'value' => 'buitenland',
               'label' => 'Buitenland reis',
               'description' => 'O.a. vakantie, zakenreis, bezoek familie/vrienden, groepsreis',
               'group' => 'transport',
               'suggestionGroup' => 'buitenland',
               'name' => 'buitenland',
            ),
            18 =>
            (object) array(
               'value' => 'zee_transport',
               'label' => 'Schip / Zee- en binnenvaart / Haven',
               'description' => 'O.a. zee-, binnenvaart vracht, zee-, riviercruise',
               'group' => 'transport',
               'suggestionGroup' => 'zee_transport',
               'name' => 'zeeTransport',
            ),
            19 =>
            (object) array(
               'value' => 'vlieg_transport',
               'label' => 'Vliegreis / Luchthaven',
               'description' => null,
               'group' => 'transport',
               'suggestionGroup' => 'vlieg_transport',
               'name' => 'vliegTransport',
            ),
            20 =>
            (object) array(
               'value' => 'transport_overige',
               'label' => 'Overig transport',
               'description' => 'O.a openbaar vervoer, taxi, treinstation, tankstation',
               'group' => 'transport',
               'suggestionGroup' => 'reizen_vervoer_overig',
               'name' => 'transportOverige',
            ),
            21 =>
            (object) array(
               'value' => 'thuis',
               'label' => 'Thuissituatie',
               'description' => 'Ziektegevallen huisgenoten of niet-samenwonende partner',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'thuis',
            ),
            22 =>
            (object) array(
               'value' => 'bezoek',
               'label' => 'Bezoek in de thuissituatie',
               'description' => 'O.a. van of bij familie, vrienden',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'bezoek',
            ),
            23 =>
            (object) array(
               'value' => 'groep',
               'label' => 'Studentenhuis',
               'description' => 'O.a. zelfstandig of gedeelde voorzieningen',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'groep',
            ),
            24 =>
            (object) array(
               'value' => 'feest',
               'label' => 'Feest / Groepsbijeenkomst privésfeer',
               'description' => 'O.a. borrel, etentje, verjaardag',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'feest',
            ),
            25 =>
            (object) array(
               'value' => 'bruiloft',
               'label' => 'Bruiloft',
               'description' => 'Alleen wanneer niet bij restaurant / evenentenlocatie / kerk',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'bruiloft',
            ),
            26 =>
            (object) array(
               'value' => 'uitvaart',
               'label' => 'Uitvaart',
               'description' => 'O.a. ceremonie, receptie, wake',
               'group' => 'thuis',
               'suggestionGroup' => 'thuissituatie_bezoek',
               'name' => 'uitvaart',
            ),
            27 =>
            (object) array(
               'value' => 'religie',
               'label' => 'Religieuze bijeenkomst',
               'description' => 'O.a. kerkdienst, suikerfeest, doop, bar mitswa',
               'group' => 'vereniging',
               'suggestionGroup' => 'religieuze_bijeenkomst',
               'name' => 'religie',
            ),
            28 =>
            (object) array(
               'value' => 'koor',
               'label' => 'Koor',
               'description' => null,
               'group' => 'vereniging',
               'suggestionGroup' => 'koor',
               'name' => 'koor',
            ),
            29 =>
            (object) array(
               'value' => 'studentenverening',
               'label' => 'Studentenverening-activiteiten',
               'description' => 'O.a. introductieweek, feest, kamp, borrel, vergadering',
               'group' => 'vereniging',
               'suggestionGroup' => 'studentenverening',
               'name' => 'studentenverening',
            ),
            30 =>
            (object) array(
               'value' => 'sport',
               'label' => 'Sportclub/ -school',
               'description' => 'O.a. binnen- en buitensport, sportkantine- en hal',
               'group' => 'vereniging',
               'suggestionGroup' => 'sport',
               'name' => 'sport',
            ),
            31 =>
            (object) array(
               'value' => 'vereniging_overige',
               'label' => 'Overige verenigingen',
               'description' => 'O.a schilderclub, muziekschool, fotografieclub, scouting',
               'group' => 'vereniging',
               'suggestionGroup' => 'vereniging_overige',
               'name' => 'verenigingOverige',
            ),
            32 =>
            (object) array(
               'value' => 'verpleeghuis',
               'label' => 'Verpleeghuis of woonzorgcentrum',
               'description' => 'O.a. revalidatiecentrum, gemengd wonen, verzorgingshuis',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'verpleeghuis',
            ),
            33 =>
            (object) array(
               'value' => 'instelling',
               'label' => 'Instelling voor verstandelijk en/of lichamelijk beperkten',
               'description' => 'Ook logeerhuis',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'instelling',
            ),
            34 =>
            (object) array(
               'value' => 'ggz_instelling',
               'label' => 'GGZ-instelling (geestelijke gezondheidszorg)',
               'description' => 'O.a. geriatrische psychiatrie, PAAZ, intramurale verslavingszorg',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'ggzInstelling',
            ),
            35 =>
            (object) array(
               'value' => 'begeleid',
               'label' => 'Begeleid kleinschalig wonen',
               'description' => 'O.a. psychiatrie, ouderenzorg, beschermd wonen',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'begeleid',
            ),
            36 =>
            (object) array(
               'value' => 'dagopvang',
               'label' => 'Dagopvang',
               'description' => 'O.a. psychiatrie, ouderen, verstandelijk of lichamelijk beperkten',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'dagopvang',
            ),
            37 =>
            (object) array(
               'value' => 'thuiszorg',
               'label' => 'Thuiszorg',
               'description' => 'Ook kraamzorg',
               'group' => 'vvt',
               'suggestionGroup' => 'zorg',
               'name' => 'thuiszorg',
            ),
            38 =>
            (object) array(
               'value' => 'ziekenhuis',
               'label' => 'Ziekenhuis',
               'description' => 'O.a. spoedeisende hulp, polikliniek',
               'group' => 'zorg',
               'suggestionGroup' => 'zorg_overig',
               'name' => 'ziekenhuis',
            ),
            39 =>
            (object) array(
               'value' => 'huisarts',
               'label' => 'Huisartsen praktijk',
               'description' => null,
               'group' => 'zorg',
               'suggestionGroup' => 'zorg_overig',
               'name' => 'huisarts',
            ),
            40 =>
            (object) array(
               'value' => 'zorg_overig',
               'label' => 'Overige gezondheidzorg',
               'description' => 'O.a. tandarts, fysiotherapie, psycholoog, ambulance, jeugdzorg',
               'group' => 'zorg',
               'suggestionGroup' => 'zorg_overig',
               'name' => 'zorgOverig',
            ),
            41 =>
            (object) array(
               'value' => 'omgeving_buiten',
               'label' => 'Omgeving (buiten)',
               'description' => 'O.a. natuurgebied, bos, park, plein',
               'group' => 'overig',
               'suggestionGroup' => 'overig',
               'name' => 'omgevingBuiten',
            ),
            42 =>
            (object) array(
               'value' => 'water_buiten',
               'label' => 'Water (buiten)',
               'description' => 'O.a. zwemplas, strand (let op: NIET Zwembad / Sauna)',
               'group' => 'overig',
               'suggestionGroup' => 'overig',
               'name' => 'waterBuiten',
            ),
            43 =>
            (object) array(
               'value' => 'dieren',
               'label' => 'Dieren',
               'description' => 'O.a. kinderboerderij of dierenarts',
               'group' => 'overig',
               'suggestionGroup' => 'overig',
               'name' => 'dieren',
            ),
            44 =>
            (object) array(
               'value' => 'overig',
               'label' => 'Overig',
               'description' => 'Alleen als context niet onder andere categorie past',
               'group' => 'overig',
               'suggestionGroup' => 'overig',
               'name' => 'overig',
            ),
            45 =>
            (object) array(
               'value' => 'vleesverwerking_slachthuis',
               'label' => 'Vleesverwerking / Slachthuis',
               'description' => null,
               'group' => 'anders',
               'suggestionGroup' => 'vleesverwerking_slachthuis',
               'name' => 'vleesverwerkingSlachthuis',
            ),
            46 =>
            (object) array(
               'value' => 'land_en_tuinbouw',
               'label' => 'Land- en tuinbouw',
               'description' => 'O.a. kas, kwekerij, veeteelt, (bloemen)veiling ',
               'group' => 'anders',
               'suggestionGroup' => 'land_en_tuinbouw',
               'name' => 'landEnTuinbouw',
            ),
            47 =>
            (object) array(
               'value' => 'bouw',
               'label' => 'Bouw',
               'description' => 'O.a. project binnen of buiten',
               'group' => 'anders',
               'suggestionGroup' => 'bouw',
               'name' => 'bouw',
            ),
            48 =>
            (object) array(
               'value' => 'fabriek',
               'label' => 'Fabriek',
               'description' => 'O.a. voeding, textiel, hout, electronica',
               'group' => 'anders',
               'suggestionGroup' => 'fabriek',
               'name' => 'fabriek',
            ),
            49 =>
            (object) array(
               'value' => 'kantoor_overige_branche',
               'label' => 'Kantoor overige branche',
               'description' => 'Alleen als context niet onder andere categorie past',
               'group' => 'anders',
               'suggestionGroup' => 'kantoor_overige_branche',
               'name' => 'kantoorOverigeBranche',
            ),
            50 =>
            (object) array(
               'value' => 'overige_andere_werkplek',
               'label' => 'Overige andere werkplek',
               'description' => 'Alleen als context niet onder andere categorie past',
               'group' => 'anders',
               'suggestionGroup' => 'overige_andere_werkplek',
               'name' => 'overigeAndereWerkplek',
            ),
            51 =>
            (object) array(
               'value' => 'onbekend',
               'label' => 'Onbekend',
               'description' => null,
               'group' => 'onbekend',
               'suggestionGroup' => 'overig',
               'name' => 'onbekend',
            ),
          ),
        );
    }
}
