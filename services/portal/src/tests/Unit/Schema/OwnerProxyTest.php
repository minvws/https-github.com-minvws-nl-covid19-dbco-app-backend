<?php

declare(strict_types=1);

namespace Tests\Unit\Schema;

use App\Schema\Entity;
use App\Schema\OwnerProxy;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('schema')]
#[Group('schema-entity')]
#[Group('owner-proxy')]
class OwnerProxyTest extends UnitTestCase
{
    private Schema $schema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schema = new Schema(Entity::class);
        $this->schema->setCurrentVersion(1);
        $this->schema->add(StringType::createField('value'));
    }

    public function testShouldForwardToAttachedOwner(): void
    {
        $proxy = new OwnerProxy();

        $owner = $this->schema->getVersion(1)->newInstance();
        $this->assertNull($owner->value);

        $proxy->attachOwner($owner);
        $this->assertNull($owner->value);

        $proxy->value = 'foo';
        $this->assertEquals('foo', $owner->value);

        $owner->value = 'bar';
        $this->assertEquals('bar', $proxy->value);
    }

    public function testShouldReturnPendingUpdatesWhileNotAttached(): void
    {
        $proxy = new OwnerProxy();

        $this->assertNull($proxy->value);

        $proxy->value = 'foo';
        $this->assertEquals('foo', $proxy->value);

        $proxy->value = 'bar';
        $this->assertEquals('bar', $proxy->value);
    }

    public function testAttachShouldApplyPendingUpdates(): void
    {
        $proxy = new OwnerProxy();

        $proxy->value = 'foo';

        $owner = $this->schema->getVersion(1)->newInstance();
        $this->assertNull($owner->value);

        $proxy->attachOwner($owner);
        $this->assertEquals('foo', $owner->value);
    }

    public function testInvalidFieldShouldThrowErrorOnAttach(): void
    {
        $proxy = new OwnerProxy();

        $proxy->foo = 'bar';

        $owner = $this->schema->getVersion(1)->newInstance();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field: foo');
        $proxy->attachOwner($owner);
    }

    public function testFieldsShouldBeNullAfterDetach(): void
    {
        $proxy = new OwnerProxy();

        $proxy->value = 'foo';
        $this->assertEquals('foo', $proxy->value);

        $owner = $this->schema->getVersion(1)->newInstance();
        $proxy->attachOwner($owner);
        $this->assertEquals('foo', $proxy->value);

        $proxy->detachOwner();
        $this->assertNull($proxy->value);
    }

    public function testGetOwner(): void
    {
        $proxy = new OwnerProxy();

        $this->assertNull($proxy->getOwner());

        $owner = $this->schema->getVersion(1)->newInstance();
        $proxy->attachOwner($owner);
        $this->assertSame($owner, $proxy->getOwner());

        $proxy->detachOwner();
        $this->assertNull($proxy->getOwner());
    }
}
