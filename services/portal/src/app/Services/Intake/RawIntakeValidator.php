<?php

declare(strict_types=1);

namespace App\Services\Intake;

use App\Exceptions\IntakeException;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Intake\RawIntake;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;
use App\Schema\Validation\ValidationContext;
use App\Schema\Validation\ValidationRules;
use App\Schema\Validation\Validator;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\Gender;
use Psr\Log\LoggerInterface;

use function sprintf;

class RawIntakeValidator
{
    private IntakeConfig $intakeConfig;
    private LoggerInterface $logger;

    public function __construct(IntakeConfig $intakeConfig, LoggerInterface $logger)
    {
        $this->intakeConfig = $intakeConfig;
        $this->logger = $logger;
    }

    /**
     * @throws IntakeException
     */
    public function validateRawIntake(RawIntake $intake): RawIntake
    {
        $this->logger->info(sprintf('Validating raw intake "%s"...', $intake->getId()));

        $identityData = $this->validateData($intake->getIdentityData(), $this->getIdentityDataValidationRules());
        $intakeData = $this->validateData($intake->getIntakeData(), $this->getIntakeDataValidationRules());
        $handoverData = $intake->getHandoverData() === null ? null : $this->validateData(
            $intake->getHandoverData(),
            $this->getHandoverDataValidationRules(),
        );

        return new RawIntake(
            $intake->getId(),
            $intake->getType(),
            $intake->getSource(),
            $identityData,
            $intakeData,
            $handoverData,
            $intake->getReceivedAt(),
        );
    }

    private function getIdentityDataValidationRules(): ValidationRules
    {
        $rules = new ValidationRules();
        $rules->addChild(ValidationRules::create(['required', 'string']), 'censored_bsn');
        $rules->addChild(ValidationRules::create(['required', 'string']), 'guid');
        $rules->addChild(ValidationRules::create(['required', 'string']), 'pc3');
        $rules->addChild(ValidationRules::create(['required', 'string']), 'ggd_region');
        $rules->addChild(ValidationRules::create(['required', 'string']), 'birth_date');
        $rules->addChild(ValidationRules::create(['present', 'string', 'nullable']), 'first_name');
        $rules->addChild(ValidationRules::create(['present', 'string', 'nullable']), 'last_name');
        $rules->addChild(ValidationRules::create(['present', 'string', 'nullable']), 'prefix');
        $rules->addChild(ValidationRules::create([
            'required',
            'string',
            Rule::in(Gender::allValuesForProperty('mittensCode')),
        ]), 'gender');
        return $rules;
    }

    private function copyFragmentRules(SchemaVersion $ownerSchemaVersion, string $fragment, array $fields): ValidationRules
    {
        $rules = new ValidationRules();

        $schemaVersion = $ownerSchemaVersion
            ->getExpectedField($fragment)
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion();

        foreach ($fields as $field) {
            $rules->addChild(
                $schemaVersion->getExpectedField($field)->getValidationRules(),
                $field,
            );
        }

        return $rules;
    }

    private function getIntakeDataValidationRules(): ValidationRules
    {
        $caseSchemaVersion = EloquentCase::getSchema()->getVersion($this->intakeConfig->getVersionForCase());
        $contactSchemaVersion = EloquentTask::getSchema()->getVersion($this->intakeConfig->getVersionForContact());

        $rules = new ValidationRules();

        // add case fragment rules
        foreach ($this->intakeConfig->getAllowedCaseFragments() as $fragment) {
            $fragmentRules = $this->copyFragmentRules(
                $caseSchemaVersion,
                $fragment,
                $this->intakeConfig->getAllowedFieldsForCaseFragment($fragment),
            );

            if ($this->intakeConfig->isFragmentRequired($fragment)) {
                $fragmentRules->addFatal('required');
            }

            $rules->addChild($fragmentRules, $fragment);
        }

        // add contact fragment rules
        $contactRules = new ValidationRules();
        foreach ($this->intakeConfig->getAllowedContactFragments() as $fragment) {
            $fragmentRules = $this->copyFragmentRules(
                $contactSchemaVersion,
                $fragment,
                $this->intakeConfig->getAllowedFieldsForContactFragment($fragment),
            );

            $contactRules->addChild($fragmentRules, $fragment);
        }

        $contactsRules = new ValidationRules();
        $contactsRules->addChild($contactRules, '*');
        $rules->addChild($contactsRules, 'contacts');

        $metaRules = new ValidationRules();
        $metaRules->addChild(ValidationRules::create(['integer', 'nullable']), 'cat1Count');
        $metaRules->addChild(ValidationRules::create(['integer']), 'estimatedCat2Count');
        $rules->addChild($metaRules, 'meta');

        return $rules;
    }

    private function getHandoverDataValidationRules(): ValidationRules
    {
        $rules = new ValidationRules();
        $rules->addChild(ValidationRules::create(['string']), 'testMonsterNumber');
        $rules->addChild(ValidationRules::create(['string']), 'ggdRegion');
        return $rules;
    }

    /**
     * @throws IntakeException
     */
    private function validateData(array $data, ValidationRules $rules): array
    {
        $validator = new Validator(null, $rules);
        $validator->setLevels([ValidationContext::FATAL]);
        $result = $validator->validate($data);

        if (!$result->isLevelValid(ValidationContext::FATAL)) {
            if ($result->getValidator(ValidationContext::FATAL) !== null) {
                $this->logger->info('Received invalid intake data', [
                    'error' => $result->getValidator(ValidationContext::FATAL)->errors(),
                ]);
            }

            throw new IntakeException('Received invalid intake data');
        }

        return $result->validated(ValidationContext::FATAL);
    }
}
