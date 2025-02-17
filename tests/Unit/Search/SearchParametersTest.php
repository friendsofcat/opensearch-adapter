<?php

declare(strict_types=1);

namespace OpenSearch\Adapter\Tests\Unit\Search;

use OpenSearch\Adapter\Search\SearchParameters;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \OpenSearch\Adapter\Search\SearchParameters
 */
final class SearchParametersTest extends TestCase
{
    public function test_array_casting_with_indices(): void
    {
        $searchParameters = (new SearchParameters())->indices(['foo', 'bar']);
        $this->assertSame(['index' => 'foo,bar'], $searchParameters->toArray());
    }

    public function test_array_casting_with_query(): void
    {
        $searchParameters = (new SearchParameters())->query([
            'term' => [
                'user' => 'foo',
            ],
        ]);

        $this->assertSame([
            'body' => [
                'query' => [
                    'term' => [
                        'user' => 'foo',
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_highlight(): void
    {
        $searchParameters = (new SearchParameters())->highlight([
            'fields' => [
                'content' => new stdClass(),
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'highlight' => [
                    'fields' => [
                        'content' => new stdClass(),
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_sort(): void
    {
        $searchParameters = (new SearchParameters())->sort([
            ['title' => 'asc'],
            '_score',
        ]);

        $this->assertEquals([
            'body' => [
                'sort' => [
                    ['title' => 'asc'],
                    '_score',
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_rescore(): void
    {
        $searchParameters = (new SearchParameters())->rescore([
            'window_size' => 50,
            'query' => [
                'rescore_query' => [
                    'match_phrase' => [
                        'message' => [
                            'query' => 'the quick brown',
                            'slop' => 2,
                        ],
                    ],
                ],
                'query_weight' => 0.7,
                'rescore_query_weight' => 1.2,
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'rescore' => [
                    'window_size' => 50,
                    'query' => [
                        'rescore_query' => [
                            'match_phrase' => [
                                'message' => [
                                    'query' => 'the quick brown',
                                    'slop' => 2,
                                ],
                            ],
                        ],
                        'query_weight' => 0.7,
                        'rescore_query_weight' => 1.2,
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_from(): void
    {
        $searchParameters = (new SearchParameters())->from(10);

        $this->assertEquals([
            'body' => [
                'from' => 10,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_size(): void
    {
        $searchParameters = (new SearchParameters())->size(100);

        $this->assertEquals([
            'body' => [
                'size' => 100,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_suggest(): void
    {
        $searchParameters = (new SearchParameters())->suggest([
            'color_suggestion' => [
                'text' => 'red',
                'term' => [
                    'field' => 'color',
                ],
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'suggest' => [
                    'color_suggestion' => [
                        'text' => 'red',
                        'term' => [
                            'field' => 'color',
                        ],
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function sourceProvider(): array
    {
        return [
            [false],
            ['obj1.*'],
            [['obj1.*', 'obj2.*']],
            [['includes' => ['obj1.*', 'obj2.*'], 'excludes' => ['*.description']]],
        ];
    }

    /**
     * @dataProvider sourceProvider
     *
     * @param array|string|bool $source
     */
    public function test_array_casting_with_source($source): void
    {
        $searchParameters = (new SearchParameters())->source($source);

        $this->assertEquals([
            'body' => [
                '_source' => $source,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_collapse(): void
    {
        $searchParameters = (new SearchParameters())->collapse([
            'field' => 'user',
        ]);

        $this->assertEquals([
            'body' => [
                'collapse' => [
                    'field' => 'user',
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_aggregations(): void
    {
        $searchParameters = (new SearchParameters())->aggregations([
            'min_price' => [
                'min' => [
                    'field' => 'price',
                ],
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'aggregations' => [
                    'min_price' => [
                        'min' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_post_filter(): void
    {
        $searchParameters = (new SearchParameters())->postFilter([
            'term' => [
                'color' => 'red',
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'post_filter' => [
                    'term' => [
                        'color' => 'red',
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_track_total_hits(): void
    {
        $searchParameters = (new SearchParameters())->trackTotalHits(100);

        $this->assertEquals([
            'body' => [
                'track_total_hits' => 100,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_indices_boost(): void
    {
        $searchParameters = (new SearchParameters())->indicesBoost([
            ['my-alias' => 1.4],
            ['my-index' => 1.3],
        ]);

        $this->assertEquals([
            'body' => [
                'indices_boost' => [
                    ['my-alias' => 1.4],
                    ['my-index' => 1.3],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_track_scores(): void
    {
        $searchParameters = (new SearchParameters())->trackScores(true);

        $this->assertEquals([
            'body' => [
                'track_scores' => true,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_script_fields(): void
    {
        $searchParameters = (new SearchParameters())->scriptFields([
            'my_doubled_field' => [
                'script' => [
                    'lang' => 'painless',
                    'source' => 'doc[params.field] * params.multiplier',
                    'params' => [
                        'field' => 'my_field',
                        'multiplier' => 2,
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            'body' => [
                'script_fields' => [
                    'my_doubled_field' => [
                        'script' => [
                            'lang' => 'painless',
                            'source' => 'doc[params.field] * params.multiplier',
                            'params' => [
                                'field' => 'my_field',
                                'multiplier' => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_min_score(): void
    {
        $searchParameters = (new SearchParameters())->minScore(0.5);

        $this->assertEquals([
            'body' => [
                'min_score' => 0.5,
            ],
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_search_type(): void
    {
        $searchParameters = (new SearchParameters())->searchType('query_then_fetch');

        $this->assertEquals([
            'search_type' => 'query_then_fetch',
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_preference(): void
    {
        $searchParameters = (new SearchParameters())->preference('_local');

        $this->assertEquals([
            'preference' => '_local',
        ], $searchParameters->toArray());
    }

    public function test_array_casting_with_routing(): void
    {
        $searchParameters = (new SearchParameters())->routing(['foo', 'bar']);
        $this->assertSame(['routing' => 'foo,bar'], $searchParameters->toArray());
    }

    public function test_array_casting_with_explain(): void
    {
        $searchParametersOne = (new SearchParameters())->explain();
        $this->assertSame(['explain' => true], $searchParametersOne->toArray());

        $searchParametersTwo = (new SearchParameters())->explain(true);
        $this->assertSame(['explain' => true], $searchParametersTwo->toArray());

        $searchParametersThree = (new SearchParameters())->explain(false);
        $this->assertSame(['explain' => false], $searchParametersThree->toArray());
    }
}
