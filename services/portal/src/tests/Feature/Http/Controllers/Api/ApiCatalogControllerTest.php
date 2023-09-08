<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Catalog\Category;
use App\Models\Catalog\Filter;
use App\Models\Eloquent\EloquentCase;
use App\Models\Purpose\Purpose;
use App\Services\Catalog\EnumTypeRepository;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Feature\Http\Controllers\Api\Dummy\DummyEntitityA;
use Tests\Feature\Http\Controllers\Api\Dummy\DummyEntitityB;
use Tests\Feature\Http\Controllers\Api\Dummy\DummyEnumA;
use Tests\Feature\Http\Controllers\Api\Dummy\DummyFragmentA;
use Tests\Feature\Http\Controllers\Api\Dummy\DummyModelA;

use function config;
use function count;
use function implode;
use function json_decode;
use function route;

#[Group('catalog')]
class ApiCatalogControllerTest extends FeatureTestCase
{
    public function testItCanReachTheApiCatalogIndexRoute(): void
    {
        //login as user
        $user = $this->createUser();
        $response = $this->be($user)->get(route('api-catalog'));
        $response->assertOk();
    }

    public function testItCanVisitTheCatalogShowTypeRoute(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->get(route('api-catalogType', [
            'categories' => 'model',
            'class' => EloquentCase::class,
            'version' => 4,
        ]));

        $response->assertOk();
    }

    //Test that the show route gives all the types for a given type

    public function testApiCatalogShowRouteReturnsAllTypesForGivenTypeAndVersion(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->get(route('api-catalogType', [
            'categories' => 'model',
            'class' => EloquentCase::class,
            'version' => 4,
        ]));
        $fields = EloquentCase::getSchema()->getVersion(4)->getFields();


        $this->assertCount(count($fields), json_decode($response->getContent())->fields);
    }

    public function testApiCatalogShowRouteDoesNotReturnTypesThatDoNotMatchGivenPurpose(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->get(route('api-catalogType', [
            'categories' => 'model',
            'class' => EloquentCase::class,
            'purpose' => Purpose::ScientificResearch->value,
            'version' => 4,
        ]));
        $fields = EloquentCase::getSchema()->getVersion(4)->getFields();
        $scientificPurposeFields = [];
        //For each field in field list, check if it has the given purpose
        foreach ($fields as $field) {
            $allPurposeDetails = $field->getPurposeSpecification()->getAllPurposeDetails();
            if ($allPurposeDetails === []) {
                continue;
            }
            foreach ($allPurposeDetails as $purposeDetail) {
                if ($purposeDetail->purpose === Purpose::ScientificResearch) {
                    $scientificPurposeFields[] = $field;
                }
            }
        }

        $this->assertCount(count($scientificPurposeFields), json_decode($response->getContent())->fields);
    }

    //Index returns all

    public function testCatalogIndexReturnsAllEntities(): void
    {
        $this->setDefaultFakeConfigs();
        $user = $this->createUser();

        $response = $this->be($user)->getJson(route('api-catalog'));
        $data = json_decode($response->getContent());
        $this->assertCount(4, $data->elements);
    }

    public function testCatalogIndexReturnsNoEntitiesOutOfCategory(): void
    {
        $this->setConfigClasses();
        $this->setConfigCatalogIndex();
        $user = $this->createUser();

        $response = $this->be($user)->getJson(route('api-catalog', [
            'categories' => 'model',
        ]));

        $data = json_decode($response->getContent());
        $this->assertCount(1, $data->elements);
        self::assertEquals('model', $data->elements[0]->type);
    }

    //Index returns all for category

    public function testCatalogIndexReturnsAllEntitiesForCategoryEntity(): void
    {
        $this->setConfigClasses();
        $this->setConfigCatalogIndex();
        $user = $this->createUser();

        $response = $this->be($user)->getJson(route('api-catalog', [
            'categories' => 'entity',
        ]));
        $data = json_decode($response->getContent());
        $this->assertCount(2, $data->elements);
    }

    public function testCatalogIndexReturnsAllEntitiesForCategoryModel(): void
    {
        $this->setConfigClasses();
        $this->setConfigCatalogIndex();

        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalog', [
            'categories' => 'model',
        ]));
        $data = json_decode($response->getContent());
        $this->assertCount(1, $data->elements);
    }

    //Index met purpose returnt alles met een veld met dat purpose

    public function testCatalogIndexReturnsAllEntitiesForPurpose(): void
    {
        $this->setConfigClasses();
        $this->setConfigCatalogIndex();
        $user = $this->createUser();

        $response = $this->be($user)->getJson(route('api-catalog', [
            'categories' => 'model',
            'purpose' => Purpose::ScientificResearch->value,
        ]));

        $data = json_decode($response->getContent());
        $this->assertCount(1, $data->elements);
        $this->assertEquals('DummyModelA', $data->elements[0]->name);
    }

    //Show returnt alle velden van een entiteit

    public function testCatalogDetailReturnsAllFields(): void
    {
        $this->setConfigClasses();
        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
        ]));

        $data = json_decode($response->getContent());
        self::assertCount(4, $data->fields);
    }

    //Show met purpose return alle velden van een entiteit met dat purpose

    public function testCatalogDetailReturnsAllFieldsForPurpose(): void
    {
        $this->setConfigClasses();
        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
            'purpose' => Purpose::ScientificResearch->value,
        ]));

        $data = json_decode($response->getContent());
        self::assertCount(2, $data->fields);
        self::assertEquals('overlap', $data->fields[0]->name);
        self::assertEquals('scientificResearch', $data->fields[1]->name);
    }

    public function testCatalogDetailReturnsNoFieldsForOtherPurpose(): void
    {
        $this->setConfigClasses();
        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
            'purpose' => Purpose::ScientificResearch->value,
        ]));

        $data = json_decode($response->getContent());
        self::assertCount(2, $data->fields);


        //Loop through fields, make sure no field exist without a ScientificResearch purpose
        foreach ($data->fields as $field) {
            $allPurposeDetails = $field->purposeSpecification->purposes;
            foreach ($allPurposeDetails as $purposeDetail) {
                if ($purposeDetail->purpose->identifier === Purpose::ScientificResearch->value) {
                    continue 2;
                }
            }

            self::fail('Field ' . $field->name . ' has no ScientificResearch purpose');
        }
    }

    public function testItCanParseTheWantsJsonHeaderOnTheIndexRoute(): void
    {
        $user = $this->createUser();

        $response = $this->be($user)->getJson(route('api-catalog'));
        $response->assertOk();
    }

    public function testItCanParseTheWantsJsonHeaderOnTheShowRoute(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
        ]));
        $response->assertOk();
    }

    public function testItReturnsNotFoundStatusOnUnknownClassParameter(): void
    {
        $this->setConfigClasses();
        $user = $this->createUser();
        $response = $this->be($user)->getJson(route('api-catalogType', [
            'class' => 'NotExistingClass',
            'version' => 1,
        ]));

        $response->assertNotFound();
    }

    public function testNonExistentPurpose(): void
    {
        $this->setConfigClasses();
        $response = $this->getJson(route('api-catalog', ['purpose' => 'NonExistentPurpose']));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testBadRequestIsReturnedOnBadRequestOnShowRoute(): void
    {
        $this->setDefaultFakeConfigs();

        $response = $this->getJson(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
            'diffToVersion' => -1,
        ]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testEnumTypeIsValidated(): void
    {
        config()->set('schema.classes', [DummyEnumA::class]);

        $response = $this->getJson(route('api-catalogType', [
            'class' => DummyEnumA::class,
            'version' => 1,
        ]));
        $response->assertOk();
    }

    public function testRequestQueryParamIsParsedToOptionsObject(): void
    {
        $this->setDefaultFakeConfigs();
        config()->set('schema.classes', [DummyEntitityA::class, DummyEntitityB::class, DummyModelA::class]);

        $response = $this->getJson(route('api-catalog', ['query' => 'FooBar']));
        $response->assertOk();
    }

    public static function categoriesDataProvider(): array
    {
        return [
            [Category::Enum],
            [Category::Entity],
            [Category::Fragment],
            [Category::Model],
        ];
    }

    #[DataProvider('categoriesDataProvider')]
    public function testOnlyElementInCategoryAreReturned(Category $type): void
    {
        $this->bindMockedEnumTypeRepository();
        $this->setDefaultFakeConfigs();

        $response = $this->getJson(route('api-catalog', ['categories' => $type->value]));

        $elements = $response->json('elements');
        foreach ($elements as $element) {
            $this->assertEquals($element['type'], $type->value);
        }
    }

    public static function combinedCategoriesDataProvider(): array
    {
        return [
            [
                [
                    Category::Enum->value,
                    Category::Model->value,
                ],
            ],
            [
                [
                    Category::Entity->value,
                    Category::Enum->value,
                ],
            ],
            [
                [
                    Category::Fragment->value,
                    Category::Entity->value,
                ],
            ],
            [
                [
                    Category::Model->value,
                    Category::Fragment->value,
                ],
            ],
        ];
    }

    #[DataProvider('combinedCategoriesDataProvider')]
    public function testOnlyElementInCategoryAreReturnedWithMultipleCategories(array $categories): void
    {
        $this->bindMockedEnumTypeRepository();
        $this->setDefaultFakeConfigs();

        $categoriesString = implode(',', $categories);
        $response = $this->getJson(route('api-catalog', ['categories' => $categoriesString]));

        $elements = $response->json('elements');
        foreach ($elements as $element) {
            $this->assertContains($element['type'], $categories);
        }
    }

    public function testEndpointReturnsJson(): void
    {
        $this->setDefaultFakeConfigs();
        $response = $this->get(route('api-catalogType', [
            'class' => DummyEntitityA::class,
            'version' => 1,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function testIndexOnlyReturnsWhitelistedSchemas(): void
    {
        $this->setDefaultFakeConfigs([DummyEntitityA::class, DummyEntitityB::class], [DummyEntitityA::class]);

        $response = $this->getJson(route('api-catalog'));
        $response->assertOk();
        self::assertEquals($response->json()['elements'][0]['class'], DummyEntitityA::class);
        self::assertCount(1, $response->json('elements'));
    }

    public function testIndexReturnsAllSchemasWhenFilterSetToAll(): void
    {
        $this->setDefaultFakeConfigs();
        $this->bindMockedEnumTypeRepository();

        $response = $this->getJson(route('api-catalog', ['filter' => Filter::All->value]));
        $response->assertOk();
        foreach ($this->getAllSchemas() as $schema) {
            $found = false;
            foreach ($response->json('elements') as $element) {
                if ($element['class'] !== $schema) {
                    continue;
                }
                $found = true;
                break;
            }
            if (!$found) {
                self::fail('Schema ' . $schema . ' is not present in response');
            }
        }
    }

    public function testCatalogIndex(): void
    {
        $user = $this->createUser();
        $response = $this->be($user)->getJson('/api/catalog');

        $classes = $response->json();
        $this->assertTrue(count($classes) > 0);
    }

    public static function enumTypeDataProvider(): array
    {
        return [
            [DummyEnumA::class, 1],
            [null, 0],
        ];
    }

    #[DataProvider('enumTypeDataProvider')]
    public function testIndexOnlyReturnsWhitelistedEnums(?string $class, int $expectedCount, array $params = []): void
    {
        $this->setDefaultFakeConfigs(configCatalogIndex: [$class]);
        $this->bindMockedEnumTypeRepository();

        $response = $this->getJson(route('api-catalog', $params));

        $response->assertOk();
        self::assertEquals($class, $response->json('elements.0.class'));
        self::assertCount($expectedCount, $response->json('elements'));
    }

    /**
     * Used to fake the registry of classes that have schemas
     */
    private function setConfigClasses(?array $classes = null): void
    {
        config()->set('schema.classes', $classes ?? [
            DummyEntitityA::class,
            DummyEntitityB::class,
            DummyModelA::class,
            DummyFragmentA::class,
        ]);
    }

    /**
     * Used to set the 'Whitelisted' Index classes in the config
     */
    private function setConfigCatalogIndex(?array $classes = null): void
    {
        config()->set('schema.root', $classes ?? $this->getAllSchemas());
    }

    public function bindMockedEnumTypeRepository(): void
    {
        $repository = new EnumTypeRepository(__DIR__ . '/Dummy/Enums/index.json', 'Tests\\Feature\\Http\\Controllers\\Api\\Dummy\\');

        $this->app->instance(EnumTypeRepository::class, $repository);
    }

    private function setDefaultFakeConfigs(?array $configClasses = null, ?array $configCatalogIndex = null): void
    {
        $this->setConfigClasses($configClasses);
        $this->setConfigCatalogIndex($configCatalogIndex);
    }

    public function getAllSchemas(): array
    {
        return [
            DummyEntitityA::class,
            DummyEntitityB::class,
            DummyModelA::class,
            DummyFragmentA::class,
            DummyEnumA::class,
        ];
    }
}
