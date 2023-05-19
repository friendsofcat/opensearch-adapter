<?php declare(strict_types=1);

namespace OpenSearch\Adapter\Tests\Unit\Search;

use OpenSearch\Adapter\Search\Explanation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenSearch\Adapter\Search\Explanation
 */
final class ExplanationTest extends TestCase
{
    public function test_value_can_be_retrieved_for_explanation(): void
    {
        $explanationOne = new Explanation([
            'value' => 4.2008432,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [],
        ]);

        $this->assertEquals(4.2008432, $explanationOne->value());

        $explanationTwo = new Explanation([
            'value' => null,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [],
        ]);

        $this->assertEquals(0, $explanationTwo->value());
    }

    public function test_description_can_be_retrieved_for_explanation(): void
    {
        $explanation = new Explanation([
            'value' => 4.2008432,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [],
        ]);

        $this->assertEquals('weight(foo:bar in 0) [PerFieldSimilarity], result of:', $explanation->description());
    }

    public function test_details_can_be_retrieved_for_explanation(): void
    {
        $explanation = new Explanation([
            'value' => 4.2008432,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [
                [
                    'value' => 0.123456,
                    'description' => 'boost',
                    'details' => [
                        [
                            'value' => 0.123456,
                            'description' => 'queryNorm',
                            'details' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertNotEmpty($explanation->details());
        $this->assertContainsOnlyInstancesOf(Explanation::class, $explanation->details());
        $this->assertNotEmpty($explanation->details()->first()->details());
        $this->assertContainsOnlyInstancesOf(Explanation::class, $explanation->details()->first()->details());
    }

    public function test_raw_representation_of_the_explanation_can_be_retrieved(): void
    {
        $explanation = new Explanation([
            'value' => 4.2008432,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [
                [
                    'value' => 0.123456,
                    'description' => 'boost',
                    'details' => [
                        [
                            'value' => 0.123456,
                            'description' => 'queryNorm',
                            'details' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'value' => 4.2008432,
            'description' => 'weight(foo:bar in 0) [PerFieldSimilarity], result of:',
            'details' => [
                [
                    'value' => 0.123456,
                    'description' => 'boost',
                    'details' => [
                        [
                            'value' => 0.123456,
                            'description' => 'queryNorm',
                            'details' => [],
                        ],
                    ],
                ],
            ],
        ], $explanation->raw());
    }
}
