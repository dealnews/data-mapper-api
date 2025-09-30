<?php

namespace DealNews\DataMapperAPI\Tests;

use \DealNews\DataMapperAPI\SearchQuery;
use PHPUnit\Framework\Attributes\DataProvider;

class SearchQueryTest extends \PHPUnit\Framework\TestCase {

    #[DataProvider('buildQueryData')]
    public function testBuildQuery($input, $expect, $exception = null, $exception_code = null, $mode = 'default') {
        if (!is_null($exception)) {
            $this->expectException($exception);
            if (!is_null($exception_code)) {
                $this->expectExceptionCode($exception_code);
            }
        }

        $sq    = new SearchQuery($mode);
        $query = $sq->prepareQuery('test_table', 'test_id', $input);

        if (is_null($exception)) {
            $this->assertSame($expect, $query);
        }
    }

    public static function buildQueryData() {
        return [

            'Invalid Mode' => [
                [],
                null,
                '\\InvalidArgumentException',
                99,
                'badmode',
            ],

            'Invalid Filter' => [
                [
                    'filter' => 'fop',
                ],
                null,
                '\\InvalidArgumentException',
                11,
            ],

            'Select All' => [
                [],
                [
                    'query'  => 'select `test_id` from `test_table`',
                    'params' => [],
                ],
            ],

            'Limit' => [
                [
                    'limit' => 100,
                ],
                [
                    'query'  => 'select `test_id` from `test_table` limit 100',
                    'params' => [],
                ],
            ],

            'Limit Numeric String' => [
                [
                    'limit' => '100',
                    'start' => '10',
                ],
                [
                    'query'  => 'select `test_id` from `test_table` limit 10, 100',
                    'params' => [],
                ],
            ],

            'Limit with Start' => [
                [
                    'start' => 10,
                    'limit' => 100,
                ],
                [
                    'query'  => 'select `test_id` from `test_table` limit 10, 100',
                    'params' => [],
                ],
            ],

            'Postgres Mode' => [
                [
                    'start' => 10,
                    'limit' => 100,
                ],
                [
                    'query'  => 'select "test_id" from "test_table" limit 100 offset 10',
                    'params' => [],
                ],
                null,
                null,
                'pgsql',
            ],

            'Invalid Limit' => [
                [
                    'limit' => '',
                ],
                null,
                '\\InvalidArgumentException',
                4,
            ],

            'From Without Size' => [
                [
                    'start' => 100,
                ],
                null,
                '\\InvalidArgumentException',
                6,
            ],

            'Invalid From' => [
                [
                    'start' => '',
                ],
                null,
                '\\InvalidArgumentException',
                5,
            ],

            'Simple Sort' => [
                [
                    'sort' => [
                        'field1' => 'asc',
                        'field2' => 'desc',
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` order by `field1` asc, `field2` desc',
                    'params' => [],
                ],
            ],

            'Invalid Sort' => [
                [
                    'sort' => 'user',
                ],
                null,
                '\\InvalidArgumentException',
                7,
            ],

            'Invalid Sort Dir' => [
                [
                    'sort' => [
                        'user' => 'descending',
                    ],
                ],
                null,
                '\\InvalidArgumentException',
                10,
            ],

            'Simple Filter' => [
                [
                    'filter' => [
                        'user' => 'foo',
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `user` = :param0',
                    'params' => [
                        'param0' => 'foo',
                    ],
                ],
            ],

            'Bool Filter' => [
                [
                    'filter' => [
                        'user' => true,
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `user` = :param0',
                    'params' => [
                        'param0' => 1,
                    ],
                ],
            ],

            'Null Filter' => [
                [
                    'filter' => [
                        'user' => null,
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `user` is null',
                    'params' => [],
                ],
            ],

            'Multiple Filter' => [
                [
                    'filter' => [
                        'user'   => 'foo',
                        'field2' => 'foo',
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `user` = :param0 and `field2` = :param1',
                    'params' => [
                        'param0' => 'foo',
                        'param1' => 'foo',
                    ],
                ],
            ],

            'Multiple Values' => [
                [
                    'filter' => [
                        'user' => ['foo', 'bar'],
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `user` in (:param0, :param1)',
                    'params' => [
                        'param0' => 'foo',
                        'param1' => 'bar',
                    ],
                ],
            ],

            'Range' => [
                [
                    'filter' => [
                        'cost1' => [
                            '>=' => 1,
                        ],
                        'cost2' => [
                            '>' => 1,
                        ],
                        'cost3' => [
                            '<=' => 1,
                        ],
                        'update_date' => [
                            '<' => '2020-01-01',
                        ],
                        'create_date' => [
                            'between' => [
                                '2020-01-01',
                                '2020-01-02',
                            ],
                        ],
                    ],
                ],
                [
                    'query'  => 'select `test_id` from `test_table` where `cost1` >= :param0 and `cost2` > :param1 and `cost3` <= :param2 and `update_date` < :param3 and `create_date` between :param4 and :param5',
                    'params' => [
                        'param0' => 1,
                        'param1' => 1,
                        'param2' => 1,
                        'param3' => '2020-01-01',
                        'param4' => '2020-01-01',
                        'param5' => '2020-01-02',
                    ],
                ],
            ],

            'Bad Simple Range' => [
                [
                    'filter' => [
                        'cost1' => [
                            '>=' => [],
                        ],
                    ],
                ],
                null,
                '\\InvalidArgumentException',
                12,
            ],

            'Bad Between Range' => [
                [
                    'filter' => [
                        'cost1' => [
                            'between' => [],
                        ],
                    ],
                ],
                null,
                '\\InvalidArgumentException',
                12,
            ],

            'Bad Between Range' => [
                [
                    'filter' => [
                        'cost1' => [
                            'between' => [
                                'from' => 1,
                                'to'   => 2,
                            ],
                        ],
                    ],
                ],
                null,
                '\\InvalidArgumentException',
                12,
            ],

        ];
    }
}
