<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\SearchObjects;
use \DealNews\DataMapperAPI\SearchQuery;
use DealNews\DB\PDO;

class SearchObjectsTest extends TestCase {
    public function testFound() {
        $pdo = new class extends PDO {
            public function __construct() {
                // noop
            }

            public function getAttribute($attribute): mixed {
                return 'mysql';
            }
        };

        $crud = new class($pdo) extends \DealNews\DB\CRUD {

            /**
             * Need to declare this here because PDO is readonly and cannot be set in child class
             */
            public readonly PDO $pdo;

            public function __construct($pdo) {
                $this->pdo = $pdo;
            }

            public function runFetch(string $query, array $params = []): array {
                return [
                    [
                        'test_id' => 1,
                    ],
                ];
            }
        };

        $obj  = new SearchObjects($crud);
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => json_encode([
                    'filter' => [
                        'test_id' => 1,
                    ],
                ]),
            ],
            true
        );

        $this->assertEquals(
            [
                [
                    'test_id'     => 1,
                    'description' => 'Test Object 1',
                ],
                'http_status' => 200,
            ],
            $data
        );
    }

    public function testNotFound() {
        $pdo = new class extends PDO {
            public function __construct() {
                // noop
            }

            public function getAttribute($attribute): mixed {
                return 'mysql';
            }
        };

        $crud = new class($pdo) extends \DealNews\DB\CRUD {

            /**
             * Need to declare this here because PDO is readonly and cannot be set in child class
             */
            public readonly PDO $pdo;

            public function __construct($pdo) {
                $this->pdo = $pdo;
            }

            public function runFetch(string $query, array $params = []): array {
                return [];
            }
        };

        $obj  = new SearchObjects($crud);
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => json_encode([
                    'filter' => [
                        'test_id' => 2,
                    ],
                ]),
            ],
            true
        );

        $this->assertEquals(
            [
                'http_status' => 200,
            ],
            $data
        );
    }

    public function testBadQuery() {
        $obj  = new SearchObjects();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => '{',
            ],
            false
        );
        $this->assertEquals(
            [
                'http_status' => 400,
                'error'       => 'Invalid search query',
            ],
            $data
        );
    }

    public function testNotDBMapper() {
        $obj  = new SearchObjects();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestNotDBObject',
                'post_data'   => '{}',
            ],
            false
        );
        $this->assertEquals(
            [
                'http_status' => 400,
                'error'       => 'TestNotDBObject does not support search',
            ],
            $data
        );
    }

    public function testSearchQueryException() {
        $pdo = new class extends PDO {
            public function __construct() {
                // noop
            }

            public function getAttribute($attribute): mixed {
                return 'mysql';
            }
        };

        $crud = new class($pdo) extends \DealNews\DB\CRUD {

            /**
             * Need to declare this here because PDO is readonly and cannot be set in child class
             */
            public readonly PDO $pdo;

            public function __construct($pdo) {
                $this->pdo = $pdo;
            }
        };

        $sq = new class extends SearchQuery {
            public function prepareQuery(string $table, string $field, array $query): array {
                throw new \InvalidArgumentException('test exception');
            }
        };

        $obj  = new SearchObjects($crud, $sq);
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => '{}',
            ]
        );
        $this->assertEquals(
            [
                'http_status' => 400,
                'error'       => 'Invalid search query: test exception',
            ],
            $data
        );
    }
}
