<?php

declare(strict_types=1);

namespace MinVWS\DBCO\Enum\Models;

/**
 *
 *
 * *** WARNING ***
 * This code is auto-generated. To change the items of this enum. Please edit ExpertQuestionType.json!
 *
 * @codeCoverageIgnore
 *
 * @method static ExpertQuestionType medicalSupervision() medicalSupervision() Medische Supervisie
 * @method static ExpertQuestionType conversationCoach() conversationCoach() Gesprekscoach

 * @property-read string $value
*/
final class ExpertQuestionType extends Enum
{
    /**
     * @inheritDoc
     */
    protected static function enumSchema(): object
    {
        return (object) array(
           'phpClass' => 'ExpertQuestionType',
           'tsConst' => 'expertQuestionType',
           'items' =>
          array (
            0 =>
            (object) array(
               'label' => 'Medische Supervisie',
               'value' => 'medical-supervision',
               'name' => 'medicalSupervision',
            ),
            1 =>
            (object) array(
               'label' => 'Gesprekscoach',
               'value' => 'conversation-coach',
               'name' => 'conversationCoach',
            ),
          ),
        );
    }
}
