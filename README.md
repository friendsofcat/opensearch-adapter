# OpenSearch Adapter

OpenSearch Adapter is an adapter for the official PHP OpenSearch client. It's designed to simplify basic index and document operations.

## Contents

* [Compatibility](#compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Index Management](#index-management)
* [Document Management](#document-management)
* [Point in Time Management](#point-in-time-management)

## Compatibility

The current version of OpenSearch Adapter has been tested with the following configuration:

* PHP 7.4-8.x
* OpenSearch 2.x
* Laravel 6.x-8.x

## Installation

The library can be installed via Composer:

```bash
composer require friendsofcat/opensearch-adapter
```

## Configuration

OpenSearch Adapter uses [`friendsofcat/opensearch-client`](https://github.com/friendsofcat/opensearch-client) as a dependency.
To change the client settings you need to publish the configuration file first:

```bash
php artisan vendor:publish --provider="OpenSearch\Laravel\Client\ServiceProvider"
```

In the newly created `config/opensearch.client.php` file you can define the default connection name and describe multiple
connections using configuration hashes. Please, refer to
the [opensearch-client documentation](https://github.com/friendsofcat/opensearch-client) for more details.

## Index Management

`\OpenSearch\Adapter\Indices\IndexManager` is used to manipulate indices.

### Create

Create an index, either with the default settings and mapping:

```php
$index = new \OpenSearch\Adapter\Indices\Index('my_index');

$indexManager->create($index);
```

or configured according to your needs:

```php
$mapping = (new \OpenSearch\Adapter\Indices\Mapping())
    ->text('title', [
        'boost' => 2,
    ])
    ->keyword('tag', [
        'null_value' => 'NULL'
    ])
    ->geoPoint('location')
    ->dynamicTemplate('no_doc_values', [
        'match_mapping_type' => '*',
        'mapping' => [
            'type' => '{dynamic_type}',
            'doc_values' => false,
        ],
    ]);

$settings = (new \OpenSearch\Adapter\Indices\Settings())
    ->index([
        'number_of_replicas' => 2,
        'refresh_interval' => -1
    ]);

$index = new \OpenSearch\Adapter\Indices\Index('my_index', $mapping, $settings);

$indexManager->create($index);
```

Alternatively, you can create an index using raw input:

```php
$mapping = [
    'properties' => [
        'title' => [
            'type' => 'text'
        ]
    ]
];

$settings = [
    'number_of_replicas' => 2
];

$indexManager->createRaw('my_index', $mapping, $settings);
```

### Drop

Delete an index:

```php
$indexManager->drop('my_index');
```

### Put Mapping

Update an index mapping using builder:

```php
$mapping = (new \OpenSearch\Adapter\Indices\Mapping())
    ->text('title', [
        'boost' => 2,
    ])
    ->keyword('tag', [
        'null_value' => 'NULL'
    ])
    ->geoPoint('location');

$indexManager->putMapping('my_index', $mapping);
```

or using raw input:

```php
$mapping = [
    'properties' => [
        'title' => [
            'type' => 'text'
        ]
    ]
];

$indexManager->putMappingRaw('my_index', $mapping);
```

### Put Settings

Update an index settings using builder:

```php
$settings = (new \OpenSearch\Adapter\Indices\Settings())
    ->analysis([
        'analyzer' => [
            'content' => [
                'type' => 'custom',
                'tokenizer' => 'whitespace'
            ]
        ]
    ]);

$indexManager->putSettings('my_index', $settings);
```

or using raw input:

```php
$settings = [
    'number_of_replicas' => 2
];

$indexManager->putSettingsRaw('my_index', $settings);
```

### Exists

Check if an index exists:

```php
$indexManager->exists('my_index');
```

### Open

Open an index:

```php
$indexManager->open('my_index');
```

### Close

Close an index:

```php
$indexManager->close('my_index');
```

### Put Alias

Create an alias:

```php
$alias = new \OpenSearch\Adapter\Indices\Alias('my_alias', true, [
    'term' => [
        'user_id' => 12,
    ],
]);

$indexManager->putAlias('my_index', $alias);
```

The same with raw input:

```php
$settings = [
    'is_write_index' => true,
    'filter' => [
        'term' => [
            'user_id' => 12,
        ],
    ],
];

$indexManager->putAliasRaw('my_index', 'my_alias', $settings);
```

### Get Aliases

Get index aliases:

```php
$indexManager->getAliases('my_index');
```

### Delete Alias

Delete an alias:

```php
$indexManager->deleteAlias('my_index', 'my_alias');
```

### Connection

Switch OpenSearch connection:

```php
$indexManager->connection('my_connection');
```

## Document Management

`\OpenSearch\Adapter\Documents\DocumentManager` is used to manage and search documents.

### Index

Add a document to the index:

```php
$documents = collect([
    new \OpenSearch\Adapter\Documents\Document('1', ['title' => 'foo']),
    new \OpenSearch\Adapter\Documents\Document('2', ['title' => 'bar']),
]);

$documentManager->index('my_index', $documents);
```

There is also an option to refresh index immediately:

```php
$documentManager->index('my_index', $documents, true);
```

Finally, you can set a custom routing:

```php
$routing = (new \OpenSearch\Adapter\Documents\Routing())
    ->add('1', 'value1')
    ->add('2', 'value2');

$documentManager->index('my_index', $documents, false, $routing);
```

### Delete

Remove a document from the index:

```php
$documentIds = ['1', '2'];

$documentManager->delete('my_index', $documentIds);
```

If you want the index to be refreshed immediately pass `true` as the third argument:

```php
$documentManager->delete('my_index', $documentIds, true);
```

You can also set a custom routing:

```php
$routing = (new \OpenSearch\Adapter\Documents\Routing())
    ->add('1', 'value1')
    ->add('2', 'value2');

$documentManager->delete('my_index', $documentIds, false, $routing);
```

Finally, you can delete documents using query:

```php
$documentManager->deleteByQuery('my_index', ['match_all' => new \stdClass()]);
```

### Search

Search documents in the index:

```php
// configure search parameters
$searchParameters = new \OpenSearch\Adapter\Search\SearchParameters();

// specify indices to search in
$searchParameters->indices(['my_index1', 'my_index2']);

// define the query
$searchParameters->query([
    'match' => [
        'message' => 'test'
    ]
]);

// configure highlighting
$searchParameters->highlight([
    'fields' => [
        'message' => [
            'type' => 'plain',
            'fragment_size' => 15,
            'number_of_fragments' => 3,
            'fragmenter' => 'simple'
        ]
    ]
]);

// add suggestions
$searchParameters->suggest([
    'message_suggest' => [
        'text' => 'test',
        'term' => [
            'field' => 'message'
        ]
    ]
]);

// enable source filtering
$searchParameters->source(['message', 'post_date']);

// retrieve score explanation
$searchParamaters->explain();

// collapse fields
$searchParameters->collapse([
    'field' => 'user'
]);

// aggregate data
$searchParameters->aggregations([
    'max_likes' => [
        'max' => [
            'field' => 'likes'
        ]
    ]
]);

// sort documents
$searchParameters->sort([
    ['post_date' => ['order' => 'asc']],
    '_score'
]);

// rescore documents
$searchParameters->rescore([
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
    ]
]);

// add a post filter
$searchParameters->postFilter([
    'term' => [
        'cover' => 'hard'
    ]
]);

// track total hits
$searchParameters->trackTotalHits(true);

// track scores
$searchParameters->trackScores(true);

// script fields
$searchParameters->scriptFields([
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

// boost indices
$searchParameters->indicesBoost([
    ['my-alias' => 1.4],
    ['my-index' => 1.3],
]);

// define the search type
$searchParameters->searchType('query_then_fetch');

// set the preference
$searchParameters->preference('_local');

// use pagination
$searchParameters->from(0)->size(20);

// use custom routing
$searchParameters->routing(['user1', 'user2']);

// perform the search and get the result
$searchResult = $documentManager->search($searchParameters);

// get the total number of matching documents
$total = $searchResult->total();

// get the corresponding hits
$hits = $searchResult->hits();

// every hit provides access to the related index name, the score, the document, the highlight and the inner hits
// in addition, you can get a raw representation of the hit
foreach ($hits as $hit) {
    $indexName = $hit->indexName();
    $score = $hit->score();
    $document = $hit->document();
    $highlight = $hit->highlight();
    $innerHits = $hit->innerHits();
    $innerHitsTotal = $hit->innerHitsTotal();
    $innerHitsTotal = $hit->explanation();
    $raw = $hit->raw();
}

// get suggestions
$suggestions = $searchResult->suggestions();

// get aggregations
$aggregations = $searchResult->aggregations();
```

### Connection

Switch OpenSearch connection:

```php
$documentManager->connection('my_connection');
```
