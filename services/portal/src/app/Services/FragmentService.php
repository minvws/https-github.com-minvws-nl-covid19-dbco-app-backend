<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\EloquentBaseModel;
use Exception;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;

interface FragmentService
{
    /**
     * Returns a list fragment names this service supports.
     *
     * @return array
     */
    public static function fragmentNames(): array;

    /**
     * Returns a list of fragment classes for the fragments this service supports.
     *
     * @return array Fragment classes indexed by fragment names.
     */
    public static function fragmentClasses(): array;

    /**
     * Validate single fragment.
     *
     * @param EloquentBaseModel $owner Owner entity.
     * @param array $fragmentData
     * @param array|null $validatedData
     *
     * @return array
     */
    public function validateFragment(EloquentBaseModel $owner, string $fragmentName, array $fragmentData, ?array &$validatedData): array;

    /**
     * Validate multiple fragments.
     *
     * @param EloquentBaseModel $owner Owner entity.
     * @param array $fragmentNames
     * @param array $data
     * @param array|null $validatedData
     *
     * @return array
     */
    public function validateFragments(EloquentBaseModel $owner, array $fragmentNames, array $data, ?array &$validatedData = null): array;

    /**
     * Encode single fragment.
     *
     * @param string $fragmentName Fragment name.
     * @param object $fragment Fragment object.
     *
     * @return array Fragment data.
     *
     * @throws ValueTypeMismatchException
     */
    public function encodeFragment(string $fragmentName, object $fragment): array;

    /**
     * Encode multiple fragments.
     *
     * @param array $fragments Fragments indexed by name.
     *
     * @return array Fragment data indexed by name.
     *
     * @throws ValueTypeMismatchException
     */
    public function encodeFragments(array $fragments): array;

    /**
     * Decode single fragment.
     *
     * @param string $fragmentName Fragment name.
     * @param array $data Fragment data.
     * @param object|null $fragment Decode into the given fragment instance (for partial updates).
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeFragment(string $fragmentName, array $data, ?object $fragment = null): object;

    /**
     * Decode multiple fragments.
     *
     * @param array $fragmentNames Fragment names.
     * @param array $data Fragment data.
     * @param array|null $fragments Existing fragments indexed by name (for partial updates).
     *
     * @return array
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    public function decodeFragments(array $fragmentNames, array $data, ?array $fragments = null): array;

    /**
     * Loads a single fragment.
     *
     * @param string $ownerUuid Owner entity identifier.
     * @param string $fragmentName Fragment name.
     *
     * @return object Fragment instance.
     *
     * @throws Exception Load failed.
     */
    public function loadFragment(string $ownerUuid, string $fragmentName): object;

    /**
     * Loads the fragments with the given names.
     *
     * @param string $ownerUuid Owner entity identifier.
     * @param array<string> $fragmentNames Fragment names.
     *
     * @return array Fragments indexed by fragment name.
     *
     * @throws Exception Load failed.
     */
    public function loadFragments(string $ownerUuid, array $fragmentNames): array;

    /**
     * Stores a single fragment.
     *
     * @param string $ownerUuid Owner entity identifier.
     * @param string $fragmentName Fragment name.
     * @param object $fragment Fragment.
     *
     * @throws Exception Storage failed.
     */
    public function storeFragment(string $ownerUuid, string $fragmentName, object $fragment): void;

    /**
     * Stores the given fragments.
     *
     * @param string $ownerUuid Owner entity identifier.
     * @param array $fragments Fragments indexed by the fragment name.
     *
     * @throws Exception Storage failed.
     */
    public function storeFragments(string $ownerUuid, array $fragments): void;
}
