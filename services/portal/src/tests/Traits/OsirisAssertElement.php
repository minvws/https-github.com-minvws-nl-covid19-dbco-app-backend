<?php

declare(strict_types=1);

namespace Tests\Traits;

use SimpleXMLElement;

use function sprintf;

/**
 * Assert XML tags exists in XML document / SimpleXMLElement
 *
 * Trait OsirisAssertElement
 */
trait OsirisAssertElement
{
    /**
     * Xpath select <antwoord><vraag_code/><antwoord_tekst/></antwoord> combo
     */
    private static string $XPATH_ANSWER = "//antwoord/vraag_code[text()='%s']//following-sibling::antwoord_tekst";

    /**
     * Get any tag in XML file with value
     */
    private static string $XPATH_ANY_TAG = "//%s";

    /**
     * Get any tag in XML file with value
     */
    private static string $XPATH_ROOT_TAG = "/%s";

    /**
     * Assert element does NOT exist
     */
    final protected function assertAnswerElementNotExists(string $questionCode = "", ?SimpleXMLElement $xml = null): static
    {
        $xpath = sprintf(static::$XPATH_ANSWER, $questionCode);
        $result = $xml->xpath($xpath);
        $this->assertEmpty($result, "Unexpected answer found for $questionCode");
        return $this;
    }

    /**
     * Assert element exists in XML
     */
    final protected function assertAnswerElement(string $questionCode = "", string $expectedAnswer = "", ?SimpleXMLElement $xml = null): static
    {
        $xpath = sprintf(static::$XPATH_ANSWER, $questionCode);
        $result = $xml->xpath($xpath);
        $this->assertNotEmpty($result, "Answer for $questionCode not found");
        $this->assertCount(1, $result, "Single answer expected for $questionCode");
        $this->assertEquals($expectedAnswer, (string) $result[0], "Unexpected answer value for $questionCode");
        return $this;
    }

    /**
     * Assert root element exists with value
     */
    final protected function assertRootElement(string $tag, SimpleXMLElement $xml): static
    {
        $xpath = sprintf(static::$XPATH_ROOT_TAG, $tag);
        $result = $xml->xpath($xpath);
        $this->assertNotEmpty($result, $tag . " does not exists");
        return $this;
    }

    /**
     * Assert element exists with value
     */
    final protected function assertElement(string $tag, string $expectedValue, SimpleXMLElement $xml): static
    {
        $xpath = sprintf(static::$XPATH_ANY_TAG, $tag, $expectedValue);
        $result = $xml->xpath($xpath);
        $this->assertNotEmpty($result, "Tag $tag not found");
        $this->assertCount(1, $result, "Single value expected for tag $tag");
        $this->assertEquals($expectedValue, (string) $result[0], "Unexpected value for tag $tag");
        return $this;
    }
}
