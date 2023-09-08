<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Models\CovidCase\Contracts\Validatable;
use App\Models\Eloquent\EloquentBaseModel;
use Exception;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\Decoder;
use MinVWS\Codable\Encoder;
use MinVWS\Codable\ValueNotFoundException;
use MinVWS\Codable\ValueTypeMismatchException;

use function assert;
use function class_exists;
use function ucfirst;

/**
 * Base class for fragment services.
 */
abstract class AbstractFragmentService implements FragmentService
{
    use ValidatesModels;

    /**
     * Fragment class cache.
     *
     * @var array
     */
    private static array $fragmentClasses = [];

    /**
     * Returns the namespace for fragment classes.
     */
    abstract protected static function fragmentNamespace(): string;

    /**
     * @inheritDoc
     */
    abstract public static function fragmentNames(): array;

    /** @var array<string, mixed> Contains data which can be used in validation rules */
    protected array $cachedAdditionalValidationData = [];

    /**
     * Returns the fragment class name for the given fragment name.
     *
     * @return class-string Class name.
     */
    protected static function fragmentClassForName(string $fragmentName): string
    {
        // NOTE:
        // Fragment names should be in camel case so we can simply capitalize
        // the first character for converting the name to the class name. If
        // not, you should override this method in your subclass as it is called
        // statically.
        $className = static::fragmentNamespace() . '\\' . ucfirst($fragmentName);
        assert(class_exists($className));
        return $className;
    }

    /**
     * @inheritDoc
     */
    final public static function fragmentClasses(): array
    {
        if (isset(static::$fragmentClasses[static::class])) {
            return static::$fragmentClasses[static::class];
        }

        static::$fragmentClasses = [];

        foreach (static::fragmentNames() as $name) {
            static::$fragmentClasses[static::class][$name] = static::fragmentClassForName($name);
        }

        return static::$fragmentClasses[static::class];
    }

    /**
     * @inheritDoc
     */
    public function validateFragment(
        EloquentBaseModel $owner,
        string $fragmentName,
        array $fragmentData,
        ?array &$validatedData = [],
        array $filterTags = [],
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array {
        $fragmentClass = $this->fragmentClassForName($fragmentName);
        $additionalData = $this->getAdditionalValidationData($owner, $fragmentData);

        return $this->validateModel(
            $fragmentClass,
            $fragmentData,
            $validatedData,
            Validatable::SEVERITY_LEVEL_WARNING,
            $additionalData,
            $filterTags,
            $stopOnFirstFailedSeverityLevel,
        );
    }

    /**
     * @inheritDoc
     */
    public function validateFragments(
        EloquentBaseModel $owner,
        array $fragmentNames,
        array $data,
        ?array &$validatedData = null,
        array $filterTags = [],
        bool $stopOnFirstFailedSeverityLevel = true,
    ): array {
        $result = [];
        $validatedData = [];
        foreach ($fragmentNames as $fragmentName) {
            $validatedData[$fragmentName] = null;
            $result[$fragmentName] = $this->validateFragment(
                $owner,
                $fragmentName,
                $data[$fragmentName] ?? [],
                $validatedData[$fragmentName],
                $filterTags,
                $stopOnFirstFailedSeverityLevel,
            );
        }
        return $result;
    }

    /**
     * Create fragment encoder.
     *
     * Subclasses can override this to customize the encoder.
     */
    protected function createFragmentEncoder(): Encoder
    {
        $encoder = new Encoder();
        $encoder->getContext()->setUseAssociativeArraysForObjects(true);
        return $encoder;
    }

    /**
     * Encode fragment using encoder.
     *
     * Refactored this into a separate method so the encoder can be re-used for encoding multiple fragments.
     *
     * @return array
     *
     * @throws ValueTypeMismatchException
     */
    protected function encodeFragmentUsingEncoder(Encoder $encoder, string $fragmentName, object $fragment): array
    {
        return $encoder->encode($fragment);
    }

    /**
     * @inheritDoc
     */
    public function encodeFragment(string $fragmentName, object $fragment): array
    {
        $encoder = $this->createFragmentEncoder();
        return $this->encodeFragmentUsingEncoder($encoder, $fragmentName, $fragment);
    }

    /**
     * @inheritDoc
     */
    public function encodeFragments(array $fragments): array
    {
        $encoder = $this->createFragmentEncoder();

        $result = [];
        foreach ($fragments as $fragmentName => $fragment) {
            $result[$fragmentName] = $this->encodeFragmentUsingEncoder($encoder, (string) $fragmentName, $fragment);
        }

        return $result;
    }

    /**
     * Create fragment decoder.
     *
     * Subclasses can override this to customize the decoder.
     */
    protected function createFragmentDecoder(): Decoder
    {
        return new Decoder();
    }

    /**
     * Decode fragment.
     *
     * Refactored this into a separate method so the decoder can be re-used for encoding multiple fragments.
     *
     * @param array $data
     *
     * @throws CodableException
     * @throws ValueTypeMismatchException
     * @throws ValueNotFoundException
     */
    protected function decodeFragmentUsingDecoder(
        Decoder $decoder,
        string $fragmentName,
        array $data,
        ?object $fragment = null,
    ): object {
        $fragmentClass = $this->fragmentClassForName($fragmentName);

        return $decoder->decode($data)->decodeObject($fragmentClass, $fragment);
    }

    /**
     * @inheritDoc
     */
    public function decodeFragment(string $fragmentName, array $data, ?object $fragment = null): object
    {
        $decoder = $this->createFragmentDecoder();
        return $this->decodeFragmentUsingDecoder($decoder, $fragmentName, $data, $fragment);
    }

    /**
     * @inheritDoc
     */
    public function decodeFragments(array $fragmentNames, array $data, ?array $fragments = null): array
    {
        $decoder = $this->createFragmentDecoder();

        $result = [];
        foreach ($fragmentNames as $fragmentName) {
            $result[$fragmentName] = $this->decodeFragmentUsingDecoder(
                $decoder,
                $fragmentName,
                $data[$fragmentName] ?? [],
                $fragments[$fragmentName] ?? null,
            );
        }

        return $result;
    }

    public function loadFragment(string $ownerUuid, string $fragmentName): object
    {
        $fragments = $this->loadFragments($ownerUuid, [$fragmentName]);
        return $fragments[$fragmentName];
    }

    /**
     * @inheritDoc
     */
    abstract public function loadFragments(string $ownerUuid, array $fragmentNames): array;

    public function storeFragment(string $ownerUuid, string $fragmentName, object $fragment): void
    {
        $this->storeFragments($ownerUuid, [$fragmentName => $fragment]);
    }

    /**
     * @inheritDoc
     */
    abstract public function storeFragments(string $ownerUuid, array $fragments): void;

    /**
     * Returns an array of additional Rules which help with running custom validation
     * eg based on dates relative to the case creation date.
     *
     * NOTE: This comes with a side effect, where every fragment update will execute this method causing in some
     *       cases unnecessary overhead. Ideally this should be refactored and the logic moved to the fragment itself.
     *
     * @param array<string, mixed> $fragmentData
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    abstract protected function getAdditionalValidationData(
        EloquentBaseModel $owner,
        array $fragmentData,
    ): array;
}
