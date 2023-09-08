<?php

declare(strict_types=1);

namespace Tests\Feature\Casts;

use App\Casts\RiskProfile as RiskProfileCast;
use App\Models\Policy\RiskProfile;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactRiskProfile;
use MinVWS\DBCO\Enum\Models\IndexRiskProfile;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('riskProfile')]
final class RiskProfileTest extends FeatureTestCase
{
    public function testItReturnsValueAsIs(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->randomElement(IndexRiskProfile::all());

        $result = $cast->get($riskProfile, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsTheValueAsIsOnNonString(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->boolean();

        $result = $cast->get($riskProfile, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsIndexRiskProfile(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile([
            'person_type_enum' => PolicyPersonType::index(),
        ]);

        $value = $this->faker->randomElement(IndexRiskProfile::all());

        $result = $cast->get($riskProfile, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(IndexRiskProfile::class, $value);
    }

    public function testItReturnsContactRiskProfile(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile([
            'person_type_enum' => PolicyPersonType::contact(),
        ]);

        $value = $this->faker->randomElement(ContactRiskProfile::all());

        $result = $cast->get($riskProfile, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(ContactRiskProfile::class, $value);
    }

    public function testItReturnsIndexRiskProfileWithoutGivingPolicyPersonType(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->randomElement(IndexRiskProfile::all());

        $result = $cast->get($riskProfile, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(IndexRiskProfile::class, $value);
    }

    public function testItReturnsContactRiskProfileWithoutGivingPolicyPersonType(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->randomElement(ContactRiskProfile::all());

        $result = $cast->get($riskProfile, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(ContactRiskProfile::class, $value);
    }

    public function testItThrowsExceptionWhenGivenInvalidValue(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile([
            'person_type_enum' => PolicyPersonType::index(),
        ]);

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($riskProfile, $this->faker->word(), $value, []);
    }

    public function testItThrowsExceptionWhenGivenInvalidValueWithoutGivingPolicyPersonType(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($riskProfile, $this->faker->word(), $value, []);
    }

    public function testSettingANonEnumValue(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->word();

        $result = $cast->set($riskProfile, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testSettingAEnumValue(): void
    {
        $cast = new RiskProfileCast();
        $riskProfile = new RiskProfile();

        $value = $this->faker->randomElement(ContactRiskProfile::all());

        $result = $cast->set($riskProfile, $this->faker->word(), $value, []);

        $this->assertSame($value->value, $result);
    }
}
