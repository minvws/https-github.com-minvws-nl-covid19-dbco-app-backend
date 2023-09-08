<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

final class ReportTestResultRequest
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function makeValidator(): Validator
    {
        return ValidatorFacade::make($this->data, $this->rules(), [], $this->attributes());
    }

    private function rules(): array
    {
        return [
            'orderId' => ['required', 'string'],
            'messageId' => ['required', 'string', 'numeric'],
            'ggdIdentifier' => ['required', 'string', 'numeric'],
            'person' => ['required', 'array'],
            'person.initials' => ['present', 'nullable', 'string'],
            'person.firstName' => ['present', 'nullable', 'string'],
            'person.surname' => ['required', 'string'],
            'person.bsn' => ['present', 'nullable', 'string', 'numeric'],
            'person.dateOfBirth' => ['required', 'string', 'date_format:m-d-Y'],
            'person.gender' => ['required', 'string', 'in:MAN,VROUW,NIET_GESPECIFICEERD,ONBEKEND'],
            'person.email' => ['present', 'nullable', 'string'],
            'person.telephoneNumber' => ['present', 'nullable', 'string'],
            'person.address' => ['required', 'array'],
            'person.address.streetName' => ['present', 'nullable', 'string'],
            'person.address.houseNumber' => ['present', 'nullable', 'string'],
            'person.address.houseNumberSuffix' => ['present', 'nullable', 'string'],
            'person.address.postcode' => ['required', 'string'],
            'person.address.city' => ['present', 'nullable', 'string'],
            'triage' => ['required', 'array'],
            'triage.dateOfFirstSymptom' => ['present', 'nullable', 'date_format:m-d-Y'],
            'test' => ['required', 'array'],
            'test.sampleDate' => ['required', 'date'],
            'test.resultDate' => ['required', 'date'],
            'test.sampleLocation' => ['present', 'nullable', 'string'],
            'test.sampleId' => ['required', 'string'],
            'test.typeOfTest' => ['present', 'nullable', 'string', 'in:SARS-CoV-2 Zelftest,SARS-CoV-2 PCR,SARS-CoV-2 Antigeen'],
            'test.result' => ['required', 'string', 'in:POSITIEF'],
            'test.source' => ['required', 'string', 'in:CoronIT,MeldPortaal'],
            'test.testLocation' => ['present', 'nullable', 'string'],
            'test.testLocationCategory' => ['present', 'nullable', 'string'],
        ];
    }

    private function attributes(): array
    {
        $attributes = [];
        foreach ($this->rules() as $field => $rules) {
            $attributes[$field] = $field;
        }
        return $attributes;
    }
}
