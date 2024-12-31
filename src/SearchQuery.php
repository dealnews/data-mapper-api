<?php

namespace DealNews\DataMapperAPI;

/**
 * Helper for building an SQL Query from a basic JSON Query DSL
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class SearchQuery {

    /**
     * Valid modes for building the query
     *
     * @var        array
     */
    public const MODES = [
        'default',
        'pgsql',
    ];

    /**
     * Valid comparison operators
     *
     * @var        array
     */
    public const COMPARISON_OPERATORS = [
        '<',
        '<=',
        '>',
        '>=',
        'between',
    ];

    /**
     * Query building mode
     *
     * @var string
     */
    protected string $mode;

    /**
     * Params for building prepared query
     *
     * @var array
     */
    protected array $params = [];

    /**
     * Constructs a new instance.
     *
     * @param      string    $mode   The query building mode
     *
     * @throws     \InvalidArgumentException
     */
    public function __construct(string $mode = 'default') {
        if (!in_array($mode, $this::MODES)) {
            throw new \InvalidArgumentException('Invalid mode', 99);
        }
        $this->mode = $mode;
    }

    /**
     * Converts a query DSL structure into a SQL query and array of parameters
     * for passing along to PDO::prepare()
     *
     * @param      string  $table  The table name
     * @param      string  $field  The field to select
     * @param      array   $query  The query DSL in PHP array format
     *
     * @return     array   Two part array with 'query' and 'params'
     */
    public function prepareQuery(string $table, string $field, array $query): array {
        $this->params = [];

        $limit  = $this->buildLimit($query);
        $order  = $this->buildOrder($query);
        $where  = $this->buildWhere($query);

        $sql = "select `$field` from `$table`" .
               (empty($where) ? '' : " $where") .
               (empty($order) ? '' : " $order") .
               (empty($limit) ? '' : " $limit");

        if ($this->mode === 'pgsql') {
            $sql = str_replace('`', '"', $sql);
        }

        return [
            'query'  => $sql,
            'params' => $this->params,
        ];
    }

    /**
     * Builds a where class
     *
     * @param      array   $query  The query DSL in PHP array format
     *
     * @throws     \InvalidArgumentException
     *
     * @return     string
     */
    protected function buildWhere(array $query): string {
        $where = '';

        if (array_key_exists('filter', $query) && !empty($query['filter'])) {
            if (!is_array($query['filter'])) {
                throw new \InvalidArgumentException('Invalid filter', 11);
            }

            $where = 'where ' . $this->parseFields($query['filter']);
        }

        return $where;
    }

    /**
     * Adds a parameter to the parameter list
     *
     * @param      mixed  $value  The value
     *
     * @return     string  Returns the placeholder for the query
     */
    protected function addParam($value): string {
        if (is_bool($value)) {
            $value = (int)$value;
        }
        $idx                = 'param' . count($this->params);
        $this->params[$idx] = $value;

        return ":{$idx}";
    }

    /**
     * Parses the filter portion of the Query DSL
     *
     * @param      array   $fields  The fields to filter
     *
     * @return     string
     */
    protected function parseFields(array $fields): string {
        $where_clauses = [];

        foreach ($fields as $field => $value) {
            $new_value = null;
            $operator  = '=';

            if (is_array($value)) {
                if (array_keys($value) === range(0, count($value) - 1)) {
                    $params = [];
                    foreach ($value as $v) {
                        $params[] = $this->addParam($v);
                    }
                    $operator  = 'in';
                    $new_value = '(' . implode(', ', $params) . ')';
                } else {
                    $key   = key($value);
                    $value = current($value);
                    if (in_array($key, $this::COMPARISON_OPERATORS)) {
                        $operator  = $key;
                        if ($operator === 'between') {
                            if (count($value) === 2 && array_key_exists(0, $value) && array_key_exists(1, $value)) {
                                $new_value = $this->addParam($value[0]) . ' and ' . $this->addParam($value[1]);
                            }
                        } elseif (is_scalar($value) || is_bool($value)) {
                            $new_value = $this->addParam($value);
                        }
                    }
                }
            } elseif ($value === null) {
                $operator  = 'is';
                $new_value = 'null';
            } else {
                $new_value = $this->addParam($value);
            }

            if ($new_value === null) {
                throw new \InvalidArgumentException('Invalid filter for $field', 12);
            }

            $where_clauses[] = "`$field` $operator $new_value";
        }

        $where = implode(' and ', $where_clauses);

        return $where;
    }

    /**
     * Builds the order by clause
     *
     * @param      array   $query  The query DSL in PHP array format
     *
     * @throws     \InvalidArgumentException
     *
     * @return     string
     */
    protected function buildOrder(array $query): string {
        $order = '';

        if (array_key_exists('sort', $query)) {
            $field_list = [];

            if (!is_array($query['sort'])) {
                throw new \InvalidArgumentException('Invalid value for sort', 7);
            }

            foreach ($query['sort'] as $field => $direction) {
                if (!in_array($direction, ['asc', 'desc'])) {
                    throw new \InvalidArgumentException('Invalid value for sort direction argument `$field`.', 10);
                }

                $field_list[] = "`$field` $direction";
            }

            $order = 'order by ' . implode(', ', $field_list);
        }

        return $order;
    }

    /**
     * Builds a limit clause
     *
     * @param      array   $query  The query DSL in PHP array format
     *
     * @throws     \InvalidArgumentException
     *
     * @return     string
     */
    protected function buildLimit(array $query): string {
        $limit_string = '';

        $values = filter_var_array(
            $query,
            [
                'limit' => FILTER_VALIDATE_INT,
                'start' => FILTER_VALIDATE_INT,
            ],
            false
        );

        if (array_key_exists('limit', $values) && $values['limit'] === false) {
            throw new \InvalidArgumentException('Invalid value for limit', 4);
        }

        if (array_key_exists('start', $values) && $values['start'] === false) {
            throw new \InvalidArgumentException('Invalid value for start', 5);
        }

        if (isset($values['limit'])) {
            if ($this->mode == 'pgsql') {
                $limit_string = 'limit ' . ($values['limit']);
                if (!empty($values['start'])) {
                    $limit_string .= ' offset ' . ($values['start']);
                }
            } else {
                $limit_string = 'limit';
                if (!empty($values['start'])) {
                    $limit_string .= ' ' . ($values['start']) . ',';
                }
                $limit_string .= ' ' . ($values['limit']);
            }
        } elseif (isset($values['start'])) {
            throw new \InvalidArgumentException('Setting start without limit is not supported', 6);
        }

        return $limit_string;
    }
}
