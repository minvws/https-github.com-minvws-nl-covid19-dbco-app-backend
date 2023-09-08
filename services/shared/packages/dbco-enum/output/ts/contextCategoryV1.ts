/**
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ContextCategory.json!
 */

/**
 * Context categories. values
 * All values are escaped with quotes and prefixed with VALUES_ to prevent generated errors
 * caused by unsupported characters or numeric values
 */
export enum ContextCategoryV1 {
  'VALUE_restaurant' = 'restaurant',
  'VALUE_club' = 'club',
  'VALUE_accomodatie_binnenland' = 'accomodatie_binnenland',
  'VALUE_retail' = 'retail',
  'VALUE_evenement_vast' = 'evenement_vast',
  'VALUE_evenement_zonder' = 'evenement_zonder',
  'VALUE_zwembad' = 'zwembad',
  'VALUE_horeca_overig' = 'horeca_overig',
  'VALUE_asielzoekerscentrum' = 'asielzoekerscentrum',
  'VALUE_penitentiaire_instelling' = 'penitentiaire_instelling',
  'VALUE_residentiele_jeugdinstelling' = 'residentiele_jeugdinstelling',
  'VALUE_opvang_overig' = 'opvang_overig',
  'VALUE_kinder_opvang' = 'kinder_opvang',
  'VALUE_basis_onderwijs' = 'basis_onderwijs',
  'VALUE_voortgezet_onderwijs' = 'voortgezet_onderwijs',
  'VALUE_mbo' = 'mbo',
  'VALUE_hbo_universiteit' = 'hbo_universiteit',
  'VALUE_buitenland' = 'buitenland',
  'VALUE_zee_transport' = 'zee_transport',
  'VALUE_vlieg_transport' = 'vlieg_transport',
  'VALUE_transport_overige' = 'transport_overige',
  'VALUE_thuis' = 'thuis',
  'VALUE_bezoek' = 'bezoek',
  'VALUE_groep' = 'groep',
  'VALUE_feest' = 'feest',
  'VALUE_bruiloft' = 'bruiloft',
  'VALUE_uitvaart' = 'uitvaart',
  'VALUE_religie' = 'religie',
  'VALUE_koor' = 'koor',
  'VALUE_studentenverening' = 'studentenverening',
  'VALUE_sport' = 'sport',
  'VALUE_vereniging_overige' = 'vereniging_overige',
  'VALUE_verpleeghuis' = 'verpleeghuis',
  'VALUE_instelling' = 'instelling',
  'VALUE_ggz_instelling' = 'ggz_instelling',
  'VALUE_begeleid' = 'begeleid',
  'VALUE_dagopvang' = 'dagopvang',
  'VALUE_thuiszorg' = 'thuiszorg',
  'VALUE_ziekenhuis' = 'ziekenhuis',
  'VALUE_huisarts' = 'huisarts',
  'VALUE_zorg_overig' = 'zorg_overig',
  'VALUE_omgeving_buiten' = 'omgeving_buiten',
  'VALUE_water_buiten' = 'water_buiten',
  'VALUE_dieren' = 'dieren',
  'VALUE_overig' = 'overig',
  'VALUE_vleesverwerking_slachthuis' = 'vleesverwerking_slachthuis',
  'VALUE_land_en_tuinbouw' = 'land_en_tuinbouw',
  'VALUE_bouw' = 'bouw',
  'VALUE_fabriek' = 'fabriek',
  'VALUE_kantoor_overige_branche' = 'kantoor_overige_branche',
  'VALUE_overige_andere_werkplek' = 'overige_andere_werkplek',
  'VALUE_onbekend' = 'onbekend',
}

/**
 * Context categories. options to be used in the forms
 */
export const contextCategoryV1Options = [
    {
        "label": "Restaurant / Café",
        "value": ContextCategoryV1.VALUE_restaurant,
        "description": "O.a. lunchroom, kroeg",
        "group": "horeca",
        "suggestionGroup": "horeca"
    },
    {
        "label": "Club / Disco",
        "value": ContextCategoryV1.VALUE_club,
        "description": null,
        "group": "horeca",
        "suggestionGroup": "horeca"
    },
    {
        "label": "Accommodatie binnenland",
        "value": ContextCategoryV1.VALUE_accomodatie_binnenland,
        "description": "O.a. hotel, B&B, camping, vakantiepark of -huis",
        "group": "horeca",
        "suggestionGroup": "accommodatie"
    },
    {
        "label": "Retail, detailhandel",
        "value": ContextCategoryV1.VALUE_retail,
        "description": "O.a. winkel, supermarkt, tuin- of bouwcentrum, woonboulevard",
        "group": "horeca",
        "suggestionGroup": "retail"
    },
    {
        "label": "Evenement / Attractie met vaste zitplekken",
        "value": ContextCategoryV1.VALUE_evenement_vast,
        "description": "O.a. theater, bioscoop, sportwedstrijd",
        "group": "horeca",
        "suggestionGroup": "evenement"
    },
    {
        "label": "Evenement / Attractie zonder vaste zitplekken",
        "value": ContextCategoryV1.VALUE_evenement_zonder,
        "description": "O.a. pretpark, museum, festival, dierentuin, beurs, demonstratie",
        "group": "horeca",
        "suggestionGroup": "evenement"
    },
    {
        "label": "Zwembad / Sauna",
        "value": ContextCategoryV1.VALUE_zwembad,
        "description": null,
        "group": "horeca",
        "suggestionGroup": "zwembad"
    },
    {
        "label": "Overige horeca / Retail / Entertainment",
        "value": ContextCategoryV1.VALUE_horeca_overig,
        "description": "O.a. kapper, beautysalon, massagesalon",
        "group": "horeca",
        "suggestionGroup": "horeca_overig"
    },
    {
        "label": "Asielzoekerscentrum",
        "value": ContextCategoryV1.VALUE_asielzoekerscentrum,
        "description": "O.a. aanmeld- en opvangcentrum AZC",
        "group": "opvang",
        "suggestionGroup": "maatschappelijke_opvang"
    },
    {
        "label": "Penitentiaire instelling",
        "value": ContextCategoryV1.VALUE_penitentiaire_instelling,
        "description": "O.a. huis van bewaring, tbs-kliniek, semi-residentieel volwassen",
        "group": "opvang",
        "suggestionGroup": "maatschappelijke_opvang"
    },
    {
        "label": "(Semi-)residentiele jeugdinstelling",
        "value": ContextCategoryV1.VALUE_residentiele_jeugdinstelling,
        "description": null,
        "group": "opvang",
        "suggestionGroup": "maatschappelijke_opvang"
    },
    {
        "label": "Overige maatschappelijke opvang",
        "value": ContextCategoryV1.VALUE_opvang_overig,
        "description": "O.a. thuis- en dakloos, crisis, blijf-van-mijn-lijf, verslaving",
        "group": "opvang",
        "suggestionGroup": "maatschappelijke_opvang"
    },
    {
        "label": "Kinderdagverblijf / Kinderopvang / BSO",
        "value": ContextCategoryV1.VALUE_kinder_opvang,
        "description": "Ook medisch kinderdagverblijf, peuterspeelzaal",
        "group": "onderwijs",
        "suggestionGroup": "kinderopvang"
    },
    {
        "label": "Basisonderwijs",
        "value": ContextCategoryV1.VALUE_basis_onderwijs,
        "description": "Ook speciaal basisonderwijs",
        "group": "onderwijs",
        "suggestionGroup": "onderwijs"
    },
    {
        "label": "Voortgezet Onderwijs",
        "value": ContextCategoryV1.VALUE_voortgezet_onderwijs,
        "description": "Ook speciaal voortgezet onderwijs, praktijkschool",
        "group": "onderwijs",
        "suggestionGroup": "onderwijs"
    },
    {
        "label": "MBO",
        "value": ContextCategoryV1.VALUE_mbo,
        "description": null,
        "group": "onderwijs",
        "suggestionGroup": "onderwijs"
    },
    {
        "label": "HBO of WO (Universiteit)",
        "value": ContextCategoryV1.VALUE_hbo_universiteit,
        "description": null,
        "group": "onderwijs",
        "suggestionGroup": "onderwijs"
    },
    {
        "label": "Buitenland reis",
        "value": ContextCategoryV1.VALUE_buitenland,
        "description": "O.a. vakantie, zakenreis, bezoek familie/vrienden, groepsreis",
        "group": "transport",
        "suggestionGroup": "buitenland"
    },
    {
        "label": "Schip / Zee- en binnenvaart / Haven",
        "value": ContextCategoryV1.VALUE_zee_transport,
        "description": "O.a. zee-, binnenvaart vracht, zee-, riviercruise",
        "group": "transport",
        "suggestionGroup": "zee_transport"
    },
    {
        "label": "Vliegreis / Luchthaven",
        "value": ContextCategoryV1.VALUE_vlieg_transport,
        "description": null,
        "group": "transport",
        "suggestionGroup": "vlieg_transport"
    },
    {
        "label": "Overig transport",
        "value": ContextCategoryV1.VALUE_transport_overige,
        "description": "O.a openbaar vervoer, taxi, treinstation, tankstation",
        "group": "transport",
        "suggestionGroup": "reizen_vervoer_overig"
    },
    {
        "label": "Thuissituatie",
        "value": ContextCategoryV1.VALUE_thuis,
        "description": "Ziektegevallen huisgenoten of niet-samenwonende partner",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Bezoek in de thuissituatie",
        "value": ContextCategoryV1.VALUE_bezoek,
        "description": "O.a. van of bij familie, vrienden",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Studentenhuis",
        "value": ContextCategoryV1.VALUE_groep,
        "description": "O.a. zelfstandig of gedeelde voorzieningen",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Feest / Groepsbijeenkomst privésfeer",
        "value": ContextCategoryV1.VALUE_feest,
        "description": "O.a. borrel, etentje, verjaardag",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Bruiloft",
        "value": ContextCategoryV1.VALUE_bruiloft,
        "description": "Alleen wanneer niet bij restaurant / evenentenlocatie / kerk",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Uitvaart",
        "value": ContextCategoryV1.VALUE_uitvaart,
        "description": "O.a. ceremonie, receptie, wake",
        "group": "thuis",
        "suggestionGroup": "thuissituatie_bezoek"
    },
    {
        "label": "Religieuze bijeenkomst",
        "value": ContextCategoryV1.VALUE_religie,
        "description": "O.a. kerkdienst, suikerfeest, doop, bar mitswa",
        "group": "vereniging",
        "suggestionGroup": "religieuze_bijeenkomst"
    },
    {
        "label": "Koor",
        "value": ContextCategoryV1.VALUE_koor,
        "description": null,
        "group": "vereniging",
        "suggestionGroup": "koor"
    },
    {
        "label": "Studentenverening-activiteiten",
        "value": ContextCategoryV1.VALUE_studentenverening,
        "description": "O.a. introductieweek, feest, kamp, borrel, vergadering",
        "group": "vereniging",
        "suggestionGroup": "studentenverening"
    },
    {
        "label": "Sportclub/ -school",
        "value": ContextCategoryV1.VALUE_sport,
        "description": "O.a. binnen- en buitensport, sportkantine- en hal",
        "group": "vereniging",
        "suggestionGroup": "sport"
    },
    {
        "label": "Overige verenigingen",
        "value": ContextCategoryV1.VALUE_vereniging_overige,
        "description": "O.a schilderclub, muziekschool, fotografieclub, scouting",
        "group": "vereniging",
        "suggestionGroup": "vereniging_overige"
    },
    {
        "label": "Verpleeghuis of woonzorgcentrum",
        "value": ContextCategoryV1.VALUE_verpleeghuis,
        "description": "O.a. revalidatiecentrum, gemengd wonen, verzorgingshuis",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "Instelling voor verstandelijk en/of lichamelijk beperkten",
        "value": ContextCategoryV1.VALUE_instelling,
        "description": "Ook logeerhuis",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "GGZ-instelling (geestelijke gezondheidszorg)",
        "value": ContextCategoryV1.VALUE_ggz_instelling,
        "description": "O.a. geriatrische psychiatrie, PAAZ, intramurale verslavingszorg",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "Begeleid kleinschalig wonen",
        "value": ContextCategoryV1.VALUE_begeleid,
        "description": "O.a. psychiatrie, ouderenzorg, beschermd wonen",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "Dagopvang",
        "value": ContextCategoryV1.VALUE_dagopvang,
        "description": "O.a. psychiatrie, ouderen, verstandelijk of lichamelijk beperkten",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "Thuiszorg",
        "value": ContextCategoryV1.VALUE_thuiszorg,
        "description": "Ook kraamzorg",
        "group": "vvt",
        "suggestionGroup": "zorg"
    },
    {
        "label": "Ziekenhuis",
        "value": ContextCategoryV1.VALUE_ziekenhuis,
        "description": "O.a. spoedeisende hulp, polikliniek",
        "group": "zorg",
        "suggestionGroup": "zorg_overig"
    },
    {
        "label": "Huisartsen praktijk",
        "value": ContextCategoryV1.VALUE_huisarts,
        "description": null,
        "group": "zorg",
        "suggestionGroup": "zorg_overig"
    },
    {
        "label": "Overige gezondheidzorg",
        "value": ContextCategoryV1.VALUE_zorg_overig,
        "description": "O.a. tandarts, fysiotherapie, psycholoog, ambulance, jeugdzorg",
        "group": "zorg",
        "suggestionGroup": "zorg_overig"
    },
    {
        "label": "Omgeving (buiten)",
        "value": ContextCategoryV1.VALUE_omgeving_buiten,
        "description": "O.a. natuurgebied, bos, park, plein",
        "group": "overig",
        "suggestionGroup": "overig"
    },
    {
        "label": "Water (buiten)",
        "value": ContextCategoryV1.VALUE_water_buiten,
        "description": "O.a. zwemplas, strand (let op: NIET Zwembad / Sauna)",
        "group": "overig",
        "suggestionGroup": "overig"
    },
    {
        "label": "Dieren",
        "value": ContextCategoryV1.VALUE_dieren,
        "description": "O.a. kinderboerderij of dierenarts",
        "group": "overig",
        "suggestionGroup": "overig"
    },
    {
        "label": "Overig",
        "value": ContextCategoryV1.VALUE_overig,
        "description": "Alleen als context niet onder andere categorie past",
        "group": "overig",
        "suggestionGroup": "overig"
    },
    {
        "label": "Vleesverwerking / Slachthuis",
        "value": ContextCategoryV1.VALUE_vleesverwerking_slachthuis,
        "description": null,
        "group": "anders",
        "suggestionGroup": "vleesverwerking_slachthuis"
    },
    {
        "label": "Land- en tuinbouw",
        "value": ContextCategoryV1.VALUE_land_en_tuinbouw,
        "description": "O.a. kas, kwekerij, veeteelt, (bloemen)veiling ",
        "group": "anders",
        "suggestionGroup": "land_en_tuinbouw"
    },
    {
        "label": "Bouw",
        "value": ContextCategoryV1.VALUE_bouw,
        "description": "O.a. project binnen of buiten",
        "group": "anders",
        "suggestionGroup": "bouw"
    },
    {
        "label": "Fabriek",
        "value": ContextCategoryV1.VALUE_fabriek,
        "description": "O.a. voeding, textiel, hout, electronica",
        "group": "anders",
        "suggestionGroup": "fabriek"
    },
    {
        "label": "Kantoor overige branche",
        "value": ContextCategoryV1.VALUE_kantoor_overige_branche,
        "description": "Alleen als context niet onder andere categorie past",
        "group": "anders",
        "suggestionGroup": "kantoor_overige_branche"
    },
    {
        "label": "Overige andere werkplek",
        "value": ContextCategoryV1.VALUE_overige_andere_werkplek,
        "description": "Alleen als context niet onder andere categorie past",
        "group": "anders",
        "suggestionGroup": "overige_andere_werkplek"
    },
    {
        "label": "Onbekend",
        "value": ContextCategoryV1.VALUE_onbekend,
        "description": null,
        "group": "onbekend",
        "suggestionGroup": "overig"
    }
];
