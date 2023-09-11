<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit UnderlyingSuffering.json!
 *
 * @codeCoverageIgnore
 *
 * @method static UnderlyingSuffering immuneDisorders() immuneDisorders() Aangeboren afweerstoornissen
 * @method static UnderlyingSuffering autoimmuneDisease() autoimmuneDisease() Auto-immuunziekte, waarvoor afweeronderdrukkende medicijnen
 * @method static UnderlyingSuffering bloodDisease() bloodDisease() Bloedziekte (o.a. leukemie)
 * @method static UnderlyingSuffering malignantBloodDisease() malignantBloodDisease() Kwaadaardige bloedziekte (bijv. leukemie), gediagnosticeerd in afgelopen 5 jaar
 * @method static UnderlyingSuffering cardioVascular() cardioVascular() Chronische hart- en vaatziekten
 * @method static UnderlyingSuffering chronicHeartDisease() chronicHeartDisease() Chronische hartziekte, waardoor indicatie griepprik
 * @method static UnderlyingSuffering chronic() chronic() Chronische longziekte
 * @method static UnderlyingSuffering chronicLungDisease() chronicLungDisease() Chronische longziekte, waardoor indicatie griepprik
 * @method static UnderlyingSuffering diabetes() diabetes() Diabetes (suikerziekte)
 * @method static UnderlyingSuffering diabetesUnstableGlucoselevels() diabetesUnstableGlucoselevels() Diabetes met instabiele glucosewaarden en/of met complicaties
 * @method static UnderlyingSuffering dementiaAlzheimers() dementiaAlzheimers() Dementie / Alzheimer
 * @method static UnderlyingSuffering hivUntreated() hivUntreated() HIV onbehandeld
 * @method static UnderlyingSuffering malignity() malignity() Kanker of kwaadaardige tumor
 * @method static UnderlyingSuffering solidTumor() solidTumor() Solide tumor, in de afgelopen 3 maanden behandeld met chemotherapie/immunotherapie
 * @method static UnderlyingSuffering liver() liver() Leveraandoening
 * @method static UnderlyingSuffering liverCirrhosis() liverCirrhosis() Cirrose (ernstige leverziekte)
 * @method static UnderlyingSuffering kidney() kidney() Nieraandoening
 * @method static UnderlyingSuffering kidneyDialysis() kidneyDialysis() Nierziekte, waarvoor nierdialyse (of op de wachtlijst)
 * @method static UnderlyingSuffering obesitas() obesitas() Obesitas (overgewicht, BMI hoger dan 30)
 * @method static UnderlyingSuffering morbidObesity() morbidObesity() Morbide obesitas (zeer ernstig overgewicht, BMI hoger dan 40)
 * @method static UnderlyingSuffering transplant() transplant() Orgaan, stamcel of beenmerg transplantatie
 * @method static UnderlyingSuffering organStemcellTransplant() organStemcellTransplant() Orgaan- of stamceltransplantatie
 * @method static UnderlyingSuffering parkinson() parkinson() Parkinson
 * @method static UnderlyingSuffering sicklecellDisease() sicklecellDisease() Sikkelcelziekte
 * @method static UnderlyingSuffering immuneDeficiency() immuneDeficiency() Slecht functionerend afweersysteem (incl. HIV)
 * @method static UnderlyingSuffering downSyndrome() downSyndrome() Syndroom van Down
 * @method static UnderlyingSuffering neurologicNeuromuscuklar() neurologicNeuromuscuklar() Ziekte van het zenuwstelsel (bijv. epilepsie of spierziekte zoals ALS)
 * @method static UnderlyingSuffering chronicNervoussystemDisease() chronicNervoussystemDisease() Chronische ziekte van het zenuwstelsel of spierziekte, waardoor problemen met de ademhaling

 * @property-read string $value
 * @property-read int $osirisCode
*/
final class UnderlyingSuffering extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'UnderlyingSuffering',
           'tsConst' => 'underlyingSuffering',
           'currentVersion' => 2,
           'properties' =>
          (object) array(
             'osirisCode' =>
            (object) array(
               'type' => 'int',
               'scope' => 'php',
               'phpType' => 'int',
            ),
          ),
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Aangeboren afweerstoornissen',
               'value' => 'immune-disorders',
               'osirisCode' => 18,
               'minVersion' => 2,
               'name' => 'immuneDisorders',
            ),
            1 =>
            (object) array(
               'label' => 'Auto-immuunziekte, waarvoor afweeronderdrukkende medicijnen',
               'value' => 'autoimmune-disease',
               'osirisCode' => 21,
               'minVersion' => 2,
               'name' => 'autoimmuneDisease',
            ),
            2 =>
            (object) array(
               'label' => 'Bloedziekte (o.a. leukemie)',
               'value' => 'blood-disease',
               'osirisCode' => 10,
               'maxVersion' => 1,
               'name' => 'bloodDisease',
            ),
            3 =>
            (object) array(
               'label' => 'Kwaadaardige bloedziekte (bijv. leukemie), gediagnosticeerd in afgelopen 5 jaar',
               'value' => 'malignant-blood-disease',
               'osirisCode' => 10,
               'minVersion' => 2,
               'name' => 'malignantBloodDisease',
            ),
            4 =>
            (object) array(
               'label' => 'Chronische hart- en vaatziekten',
               'value' => 'cardio-vascular',
               'osirisCode' => 3,
               'maxVersion' => 1,
               'name' => 'cardioVascular',
            ),
            5 =>
            (object) array(
               'label' => 'Chronische hartziekte, waardoor indicatie griepprik',
               'value' => 'chronic-heart-disease',
               'osirisCode' => 3,
               'minVersion' => 2,
               'name' => 'chronicHeartDisease',
            ),
            6 =>
            (object) array(
               'label' => 'Chronische longziekte',
               'value' => 'chronic',
               'osirisCode' => 9,
               'maxVersion' => 1,
               'name' => 'chronic',
            ),
            7 =>
            (object) array(
               'label' => 'Chronische longziekte, waardoor indicatie griepprik',
               'value' => 'chronic-lung-disease',
               'osirisCode' => 9,
               'minVersion' => 2,
               'name' => 'chronicLungDisease',
            ),
            8 =>
            (object) array(
               'label' => 'Diabetes (suikerziekte)',
               'value' => 'diabetes',
               'osirisCode' => 4,
               'maxVersion' => 1,
               'name' => 'diabetes',
            ),
            9 =>
            (object) array(
               'label' => 'Diabetes met instabiele glucosewaarden en/of met complicaties',
               'value' => 'diabetes-unstable-glucoselevels',
               'osirisCode' => 4,
               'minVersion' => 2,
               'name' => 'diabetesUnstableGlucoselevels',
            ),
            10 =>
            (object) array(
               'label' => 'Dementie / Alzheimer',
               'value' => 'dementia-alzheimers',
               'osirisCode' => 15,
               'name' => 'dementiaAlzheimers',
            ),
            11 =>
            (object) array(
               'label' => 'HIV onbehandeld',
               'value' => 'hiv-untreated',
               'osirisCode' => 19,
               'minVersion' => 2,
               'name' => 'hivUntreated',
            ),
            12 =>
            (object) array(
               'label' => 'Kanker of kwaadaardige tumor',
               'value' => 'malignity',
               'osirisCode' => 22,
               'maxVersion' => 1,
               'name' => 'malignity',
            ),
            13 =>
            (object) array(
               'label' => 'Solide tumor, in de afgelopen 3 maanden behandeld met chemotherapie/immunotherapie',
               'value' => 'solid-tumor',
               'osirisCode' => 22,
               'minVersion' => 2,
               'name' => 'solidTumor',
            ),
            14 =>
            (object) array(
               'label' => 'Leveraandoening',
               'value' => 'liver',
               'osirisCode' => 5,
               'maxVersion' => 1,
               'name' => 'liver',
            ),
            15 =>
            (object) array(
               'label' => 'Cirrose (ernstige leverziekte)',
               'value' => 'liver_cirrhosis',
               'osirisCode' => 5,
               'minVersion' => 2,
               'name' => 'liverCirrhosis',
            ),
            16 =>
            (object) array(
               'label' => 'Nieraandoening',
               'value' => 'kidney',
               'osirisCode' => 8,
               'maxVersion' => 1,
               'name' => 'kidney',
            ),
            17 =>
            (object) array(
               'label' => 'Nierziekte, waarvoor nierdialyse (of op de wachtlijst)',
               'value' => 'kidney-dialysis',
               'osirisCode' => 8,
               'minVersion' => 2,
               'name' => 'kidneyDialysis',
            ),
            18 =>
            (object) array(
               'label' => 'Obesitas (overgewicht, BMI hoger dan 30)',
               'value' => 'obesitas',
               'osirisCode' => 14,
               'maxVersion' => 1,
               'name' => 'obesitas',
            ),
            19 =>
            (object) array(
               'label' => 'Morbide obesitas (zeer ernstig overgewicht, BMI hoger dan 40)',
               'value' => 'morbid-obesity',
               'osirisCode' => 14,
               'minVersion' => 2,
               'name' => 'morbidObesity',
            ),
            20 =>
            (object) array(
               'label' => 'Orgaan, stamcel of beenmerg transplantatie',
               'value' => 'transplant',
               'osirisCode' => 20,
               'maxVersion' => 1,
               'name' => 'transplant',
            ),
            21 =>
            (object) array(
               'label' => 'Orgaan- of stamceltransplantatie',
               'value' => 'organ-stemcell-transplant',
               'osirisCode' => 20,
               'minVersion' => 2,
               'name' => 'organStemcellTransplant',
            ),
            22 =>
            (object) array(
               'label' => 'Parkinson',
               'value' => 'parkinson',
               'osirisCode' => 16,
               'name' => 'parkinson',
            ),
            23 =>
            (object) array(
               'label' => 'Sikkelcelziekte',
               'value' => 'sicklecell-disease',
               'osirisCode' => 23,
               'minVersion' => 2,
               'name' => 'sicklecellDisease',
            ),
            24 =>
            (object) array(
               'label' => 'Slecht functionerend afweersysteem (incl. HIV)',
               'value' => 'immune-deficiency',
               'osirisCode' => 7,
               'maxVersion' => 1,
               'name' => 'immuneDeficiency',
            ),
            25 =>
            (object) array(
               'label' => 'Syndroom van Down',
               'value' => 'down-syndrome',
               'osirisCode' => 17,
               'name' => 'downSyndrome',
            ),
            26 =>
            (object) array(
               'label' => 'Ziekte van het zenuwstelsel (bijv. epilepsie of spierziekte zoals ALS)',
               'value' => 'neurologic-neuromuscuklar',
               'maxVersion' => 1,
               'name' => 'neurologicNeuromuscuklar',
            ),
            27 =>
            (object) array(
               'label' => 'Chronische ziekte van het zenuwstelsel of spierziekte, waardoor problemen met de ademhaling',
               'value' => 'chronic-nervoussystem-disease',
               'osirisCode' => 6,
               'minVersion' => 2,
               'name' => 'chronicNervoussystemDisease',
            ),
          ),
        );
    }
}
