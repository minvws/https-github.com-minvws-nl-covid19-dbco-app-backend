<?php

declare(strict_types=1);

return [
    'personal_advice' => [
        'secure' => true,
        'template' => 'advice',
        'transport' => 'secure_mail',
        'expiry_days' => 30,
        'identity_required' => false,
        'attachments' => [
            '20220331_Bijlage_Onderzoek_GGDGHOR_nl.pdf',
            '20220419_Attachment_Contact_tracing_en.pdf',
            '20220419_Bijlage_Contactinventarisatie_BCO_nl.pdf',
            '20221004_Bijlage_Vaccineren_Index_en_COVID-19.pdf',
        ],
    ],
    'contact_infection' => [
        'secure' => true,
        'template' => 'contactInfection',
        'transport' => 'secure_mail',
        'expiry_days' => 30,
        'identity_required' => false,
        'attachments' => [
            '20211007_Bijlage_contacten_vaccinatie_en_COVID-19_GGDGHOR_nl.pdf',
        ],
    ],
    'deleted_message' => [
        'secure' => false,
        'template' => 'deletedMessage',
        'transport' => 'smtp',
        'expiry_days' => null,
        'identity_required' => false,
        'attachments' => [],
    ],
    'missed_phone' => [
        'secure' => false,
        'template' => 'missedPhone',
        'transport' => 'smtp',
        'expiry_days' => null,
        'identity_required' => false,
        'attachments' => [],
    ],
];
