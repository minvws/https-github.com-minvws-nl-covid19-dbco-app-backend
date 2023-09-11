<?php

declare(strict_types=1);

$result = [
    'operator.and' => 'en',
    'operator.or' => 'of',
    'operator.identicalTo' => '===',
    'operator.equalTo' => '==',
    'operator.greaterThan' => '>',
    'operator.greaterThanOrEqualTo' => '>=',
    'operator.lessThan' => '<',
    'operator.lessThanOrEqualTo' => '<=',
    'operator.contains' => 'bevat',
    'operator.in' => 'in',
    'operator.not' => 'niet',

    'schema.organisation.label' => 'Organisatie',
    'schema.organisation.shortDescription' => 'BCO portaal gebruikers-organisatie',
    'schema.user.label' => 'Gebruiker',
    'schema.user.shortDescription' => 'BCO portaal gebruiker',
    'schema.caseList.label' => 'Dossier lijst',
    'schema.caseList.shortDescription' => 'Lijst van dossiers zoals gebruikt voor werkverdeling',
    'schema.covidCase.label' => 'Covid dossier',
    'schema.covidCase.shortDescription' => 'Covid dossier van één besmetting',
    'schema.task.label' => 'Contact',
    'schema.task.shortDescription' => 'Een contact binnen een dossier',
    'schema.context.label' => 'Context',
    'schema.context.shortDescription' => 'Een context binnen een dossier',
    'schema.place.label' => 'Locatie',
    'schema.place.shortDescription' => 'Een locatie waar één of meer contexten in dossiers naar kunnen verwijzen',
];

$path = config('schema.readTranslationsFrom', __DIR__ . '/../../data/datacatalog.csv');

assert(is_string($path));

$fd = fopen($path, 'r');
if (!is_resource($fd)) {
    return $result;
}

while ($line = fgetcsv($fd)) {
    [$entity, $field, $label, $shortDescription] = $line;
    $description = $shortDescription;
    $result[$entity . '.' . $field . '.label'] = $label;
    $result[$entity . '.' . $field . '.shortDescription'] = $shortDescription;
    $result[$entity . '.' . $field . '.description'] = $description;
}

fclose($fd);

return $result;
