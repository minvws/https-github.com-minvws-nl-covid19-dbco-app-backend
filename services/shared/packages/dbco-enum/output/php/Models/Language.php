<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit Language.json!
 *
 * @codeCoverageIgnore
 *
 * @method static Language afr() afr() Afrikaans
 * @method static Language sqi() sqi() Albanees
 * @method static Language amh() amh() Amhaars
 * @method static Language ary() ary() Arabisch (Marokkaans, Darija)
 * @method static Language ara() ara() Arabisch (Overig)
 * @method static Language hye() hye() Armeens
 * @method static Language asm() asm() Assamees
 * @method static Language aym() aym() Aymara
 * @method static Language aze() aze() Azerbeidzjaans
 * @method static Language ben() ben() Bengaals
 * @method static Language ber() ber() Berbers
 * @method static Language bis() bis() Bislama
 * @method static Language bos() bos() Bosnisch
 * @method static Language bul() bul() Bulgaars
 * @method static Language cat() cat() Catalaans
 * @method static Language yue() yue() Chinees (Kantonees)
 * @method static Language cmn() cmn() Chinees (Mandarijn)
 * @method static Language zho() zho() Chinees (Overig)
 * @method static Language prs() prs() Dari (Afghaans)
 * @method static Language dan() dan() Deens
 * @method static Language div() div() Divehi
 * @method static Language deu() deu() Duits
 * @method static Language dzo() dzo() Dzongkha
 * @method static Language eng() eng() Engels
 * @method static Language est() est() Estisch
 * @method static Language fij() fij() Fijisch
 * @method static Language fil() fil() Filipijns
 * @method static Language fin() fin() Fins
 * @method static Language fra() fra() Frans
 * @method static Language fry() fry() Fries
 * @method static Language kat() kat() Georgisch
 * @method static Language ell() ell() Grieks
 * @method static Language kal() kal() Groenlands
 * @method static Language grn() grn() Guaraní
 * @method static Language hat() hat() Haïtiaans Creools
 * @method static Language haw() haw() Hawaïaans
 * @method static Language heb() heb() Hebreeuws
 * @method static Language hin() hin() Hindoestaans (Hindisch, Hindi)
 * @method static Language hmo() hmo() Hiri Motu
 * @method static Language hun() hun() Hongaars
 * @method static Language gle() gle() Iers
 * @method static Language isl() isl() IJslands
 * @method static Language ind() ind() Indonesisch
 * @method static Language ita() ita() Italiaans
 * @method static Language jpn() jpn() Japans
 * @method static Language kan() kan() Kannada (Kanarees, Kanara)
 * @method static Language kas() kas() Kasjmiri
 * @method static Language kaz() kaz() Kazachs
 * @method static Language khm() khm() Khmer, Cambodjaans
 * @method static Language kir() kir() Kirgizisch
 * @method static Language kur() kur() Koerdisch
 * @method static Language kor() kor() Koreaans
 * @method static Language hrv() hrv() Kroatisch
 * @method static Language lao() lao() Laotiaans
 * @method static Language lat() lat() Latijn
 * @method static Language lav() lav() Lets
 * @method static Language lit() lit() Litouws
 * @method static Language ltz() ltz() Luxemburgs
 * @method static Language mkd() mkd() Macedonisch
 * @method static Language msa() msa() Maleis
 * @method static Language mri() mri() Maori
 * @method static Language nld() nld() Nederlands
 * @method static Language nep() nep() Nepalees
 * @method static Language nor() nor() Noors
 * @method static Language ukr() ukr() Oekraïens
 * @method static Language pap() pap() Papiaments
 * @method static Language pus() pus() Pasjtoe
 * @method static Language fas() fas() Perzisch (Farsi)
 * @method static Language pol() pol() Pools
 * @method static Language por() por() Portugees
 * @method static Language roh() roh() Reto-Romaans
 * @method static Language ron() ron() Roemeens
 * @method static Language rom() rom() Romani
 * @method static Language rus() rus() Russisch
 * @method static Language srp() srp() Servisch
 * @method static Language slv() slv() Sloveens
 * @method static Language slk() slk() Slowaaks
 * @method static Language som() som() Somalisch
 * @method static Language spa() spa() Spaans
 * @method static Language sus() sus() Susu
 * @method static Language tha() tha() Thai
 * @method static Language tir() tir() Tigrinya
 * @method static Language ces() ces() Tsjechisch
 * @method static Language tur() tur() Turks
 * @method static Language urd() urd() Urdu
 * @method static Language vie() vie() Vietnamees
 * @method static Language cym() cym() Welsh
 * @method static Language bel() bel() Wit-Russisch (Belarussisch)
 * @method static Language swe() swe() Zweeds

 * @property-read string $value
*/
final class Language extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'Language',
           'tsConst' => 'language',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Afrikaans',
               'value' => 'afr',
               'name' => 'afr',
            ),
            1 =>
            (object) array(
               'label' => 'Albanees',
               'value' => 'sqi',
               'name' => 'sqi',
            ),
            2 =>
            (object) array(
               'label' => 'Amhaars',
               'value' => 'amh',
               'name' => 'amh',
            ),
            3 =>
            (object) array(
               'label' => 'Arabisch (Marokkaans, Darija)',
               'value' => 'ary',
               'name' => 'ary',
            ),
            4 =>
            (object) array(
               'label' => 'Arabisch (Overig)',
               'value' => 'ara',
               'name' => 'ara',
            ),
            5 =>
            (object) array(
               'label' => 'Armeens',
               'value' => 'hye',
               'name' => 'hye',
            ),
            6 =>
            (object) array(
               'label' => 'Assamees',
               'value' => 'asm',
               'name' => 'asm',
            ),
            7 =>
            (object) array(
               'label' => 'Aymara',
               'value' => 'aym',
               'name' => 'aym',
            ),
            8 =>
            (object) array(
               'label' => 'Azerbeidzjaans',
               'value' => 'aze',
               'name' => 'aze',
            ),
            9 =>
            (object) array(
               'label' => 'Bengaals',
               'value' => 'ben',
               'name' => 'ben',
            ),
            10 =>
            (object) array(
               'label' => 'Berbers',
               'value' => 'ber',
               'name' => 'ber',
            ),
            11 =>
            (object) array(
               'label' => 'Bislama',
               'value' => 'bis',
               'name' => 'bis',
            ),
            12 =>
            (object) array(
               'label' => 'Bosnisch',
               'value' => 'bos',
               'name' => 'bos',
            ),
            13 =>
            (object) array(
               'label' => 'Bulgaars',
               'value' => 'bul',
               'name' => 'bul',
            ),
            14 =>
            (object) array(
               'label' => 'Catalaans',
               'value' => 'cat',
               'name' => 'cat',
            ),
            15 =>
            (object) array(
               'label' => 'Chinees (Kantonees)',
               'value' => 'yue',
               'name' => 'yue',
            ),
            16 =>
            (object) array(
               'label' => 'Chinees (Mandarijn)',
               'value' => 'cmn',
               'name' => 'cmn',
            ),
            17 =>
            (object) array(
               'label' => 'Chinees (Overig)',
               'value' => 'zho',
               'name' => 'zho',
            ),
            18 =>
            (object) array(
               'label' => 'Dari (Afghaans)',
               'value' => 'prs',
               'name' => 'prs',
            ),
            19 =>
            (object) array(
               'label' => 'Deens',
               'value' => 'dan',
               'name' => 'dan',
            ),
            20 =>
            (object) array(
               'label' => 'Divehi',
               'value' => 'div',
               'name' => 'div',
            ),
            21 =>
            (object) array(
               'label' => 'Duits',
               'value' => 'deu',
               'name' => 'deu',
            ),
            22 =>
            (object) array(
               'label' => 'Dzongkha',
               'value' => 'dzo',
               'name' => 'dzo',
            ),
            23 =>
            (object) array(
               'label' => 'Engels',
               'value' => 'eng',
               'name' => 'eng',
            ),
            24 =>
            (object) array(
               'label' => 'Estisch',
               'value' => 'est',
               'name' => 'est',
            ),
            25 =>
            (object) array(
               'label' => 'Fijisch',
               'value' => 'fij',
               'name' => 'fij',
            ),
            26 =>
            (object) array(
               'label' => 'Filipijns',
               'value' => 'fil',
               'name' => 'fil',
            ),
            27 =>
            (object) array(
               'label' => 'Fins',
               'value' => 'fin',
               'name' => 'fin',
            ),
            28 =>
            (object) array(
               'label' => 'Frans',
               'value' => 'fra',
               'name' => 'fra',
            ),
            29 =>
            (object) array(
               'label' => 'Fries',
               'value' => 'fry',
               'name' => 'fry',
            ),
            30 =>
            (object) array(
               'label' => 'Georgisch',
               'value' => 'kat',
               'name' => 'kat',
            ),
            31 =>
            (object) array(
               'label' => 'Grieks',
               'value' => 'ell',
               'name' => 'ell',
            ),
            32 =>
            (object) array(
               'label' => 'Groenlands',
               'value' => 'kal',
               'name' => 'kal',
            ),
            33 =>
            (object) array(
               'label' => 'Guaraní',
               'value' => 'grn',
               'name' => 'grn',
            ),
            34 =>
            (object) array(
               'label' => 'Haïtiaans Creools',
               'value' => 'hat',
               'name' => 'hat',
            ),
            35 =>
            (object) array(
               'label' => 'Hawaïaans',
               'value' => 'haw',
               'name' => 'haw',
            ),
            36 =>
            (object) array(
               'label' => 'Hebreeuws',
               'value' => 'heb',
               'name' => 'heb',
            ),
            37 =>
            (object) array(
               'label' => 'Hindoestaans (Hindisch, Hindi)',
               'value' => 'hin',
               'name' => 'hin',
            ),
            38 =>
            (object) array(
               'label' => 'Hiri Motu',
               'value' => 'hmo',
               'name' => 'hmo',
            ),
            39 =>
            (object) array(
               'label' => 'Hongaars',
               'value' => 'hun',
               'name' => 'hun',
            ),
            40 =>
            (object) array(
               'label' => 'Iers',
               'value' => 'gle',
               'name' => 'gle',
            ),
            41 =>
            (object) array(
               'label' => 'IJslands',
               'value' => 'isl',
               'name' => 'isl',
            ),
            42 =>
            (object) array(
               'label' => 'Indonesisch',
               'value' => 'ind',
               'name' => 'ind',
            ),
            43 =>
            (object) array(
               'label' => 'Italiaans',
               'value' => 'ita',
               'name' => 'ita',
            ),
            44 =>
            (object) array(
               'label' => 'Japans',
               'value' => 'jpn',
               'name' => 'jpn',
            ),
            45 =>
            (object) array(
               'label' => 'Kannada (Kanarees, Kanara)',
               'value' => 'kan',
               'name' => 'kan',
            ),
            46 =>
            (object) array(
               'label' => 'Kasjmiri',
               'value' => 'kas',
               'name' => 'kas',
            ),
            47 =>
            (object) array(
               'label' => 'Kazachs',
               'value' => 'kaz',
               'name' => 'kaz',
            ),
            48 =>
            (object) array(
               'label' => 'Khmer, Cambodjaans',
               'value' => 'khm',
               'name' => 'khm',
            ),
            49 =>
            (object) array(
               'label' => 'Kirgizisch',
               'value' => 'kir',
               'name' => 'kir',
            ),
            50 =>
            (object) array(
               'label' => 'Koerdisch',
               'value' => 'kur',
               'name' => 'kur',
            ),
            51 =>
            (object) array(
               'label' => 'Koreaans',
               'value' => 'kor',
               'name' => 'kor',
            ),
            52 =>
            (object) array(
               'label' => 'Kroatisch',
               'value' => 'hrv',
               'name' => 'hrv',
            ),
            53 =>
            (object) array(
               'label' => 'Laotiaans',
               'value' => 'lao',
               'name' => 'lao',
            ),
            54 =>
            (object) array(
               'label' => 'Latijn',
               'value' => 'lat',
               'name' => 'lat',
            ),
            55 =>
            (object) array(
               'label' => 'Lets',
               'value' => 'lav',
               'name' => 'lav',
            ),
            56 =>
            (object) array(
               'label' => 'Litouws',
               'value' => 'lit',
               'name' => 'lit',
            ),
            57 =>
            (object) array(
               'label' => 'Luxemburgs',
               'value' => 'ltz',
               'name' => 'ltz',
            ),
            58 =>
            (object) array(
               'label' => 'Macedonisch',
               'value' => 'mkd',
               'name' => 'mkd',
            ),
            59 =>
            (object) array(
               'label' => 'Maleis',
               'value' => 'msa',
               'name' => 'msa',
            ),
            60 =>
            (object) array(
               'label' => 'Maori',
               'value' => 'mri',
               'name' => 'mri',
            ),
            61 =>
            (object) array(
               'label' => 'Nederlands',
               'value' => 'nld',
               'name' => 'nld',
            ),
            62 =>
            (object) array(
               'label' => 'Nepalees',
               'value' => 'nep',
               'name' => 'nep',
            ),
            63 =>
            (object) array(
               'label' => 'Noors',
               'value' => 'nor',
               'name' => 'nor',
            ),
            64 =>
            (object) array(
               'label' => 'Oekraïens',
               'value' => 'ukr',
               'name' => 'ukr',
            ),
            65 =>
            (object) array(
               'label' => 'Papiaments',
               'value' => 'pap',
               'name' => 'pap',
            ),
            66 =>
            (object) array(
               'label' => 'Pasjtoe',
               'value' => 'pus',
               'name' => 'pus',
            ),
            67 =>
            (object) array(
               'label' => 'Perzisch (Farsi)',
               'value' => 'fas',
               'name' => 'fas',
            ),
            68 =>
            (object) array(
               'label' => 'Pools',
               'value' => 'pol',
               'name' => 'pol',
            ),
            69 =>
            (object) array(
               'label' => 'Portugees',
               'value' => 'por',
               'name' => 'por',
            ),
            70 =>
            (object) array(
               'label' => 'Reto-Romaans',
               'value' => 'roh',
               'name' => 'roh',
            ),
            71 =>
            (object) array(
               'label' => 'Roemeens',
               'value' => 'ron',
               'name' => 'ron',
            ),
            72 =>
            (object) array(
               'label' => 'Romani',
               'value' => 'rom',
               'name' => 'rom',
            ),
            73 =>
            (object) array(
               'label' => 'Russisch',
               'value' => 'rus',
               'name' => 'rus',
            ),
            74 =>
            (object) array(
               'label' => 'Servisch',
               'value' => 'srp',
               'name' => 'srp',
            ),
            75 =>
            (object) array(
               'label' => 'Sloveens',
               'value' => 'slv',
               'name' => 'slv',
            ),
            76 =>
            (object) array(
               'label' => 'Slowaaks',
               'value' => 'slk',
               'name' => 'slk',
            ),
            77 =>
            (object) array(
               'label' => 'Somalisch',
               'value' => 'som',
               'name' => 'som',
            ),
            78 =>
            (object) array(
               'label' => 'Spaans',
               'value' => 'spa',
               'name' => 'spa',
            ),
            79 =>
            (object) array(
               'label' => 'Susu',
               'value' => 'sus',
               'name' => 'sus',
            ),
            80 =>
            (object) array(
               'label' => 'Thai',
               'value' => 'tha',
               'name' => 'tha',
            ),
            81 =>
            (object) array(
               'label' => 'Tigrinya',
               'value' => 'tir',
               'name' => 'tir',
            ),
            82 =>
            (object) array(
               'label' => 'Tsjechisch',
               'value' => 'ces',
               'name' => 'ces',
            ),
            83 =>
            (object) array(
               'label' => 'Turks',
               'value' => 'tur',
               'name' => 'tur',
            ),
            84 =>
            (object) array(
               'label' => 'Urdu',
               'value' => 'urd',
               'name' => 'urd',
            ),
            85 =>
            (object) array(
               'label' => 'Vietnamees',
               'value' => 'vie',
               'name' => 'vie',
            ),
            86 =>
            (object) array(
               'label' => 'Welsh',
               'value' => 'cym',
               'name' => 'cym',
            ),
            87 =>
            (object) array(
               'label' => 'Wit-Russisch (Belarussisch)',
               'value' => 'bel',
               'name' => 'bel',
            ),
            88 =>
            (object) array(
               'label' => 'Zweeds',
               'value' => 'swe',
               'name' => 'swe',
            ),
          ),
        );
    }
}
