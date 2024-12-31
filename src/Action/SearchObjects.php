<?php

namespace DealNews\DataMapperAPI\Action;

use \DealNews\DataMapperAPI\SearchQuery;
use \DealNews\DB\CRUD;
use \DealNews\DB\Factory;

/**
 * Search for objects using a JSON Query DSL
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class SearchObjects extends Base {

    /**
     * Type of object to delete
     *
     * @var string
     */
    protected string $object_name;

    /**
     * POST data in raw format
     *
     * @var string
     */
    protected string $post_data;

    /**
     * CRUD object
     *
     * @var CRUD
     */
    protected ?CRUD $crud;

    /**
     * SearchQuery object
     *
     * @var SearchQuery
     */
    protected ?SearchQuery $sq;

    /**
     * Constructs a new instance.
     *
     * @param      ?CRUD         $crud   Optional CRUD object
     * @param      ?SearchQuery  $sq     Optional SearchQuery object
     */
    public function __construct(CRUD $crud = null, SearchQuery $sq = null) {
        $this->crud = $crud;
        $this->sq   = $sq;
    }

    /**
     * Loads data
     *
     * @return     array
     */
    public function loadData(): array {
        $data    = [];

        $mapper = $this->repository->getMapper($this->object_name);

        // Search is only supported by objects mapped with
        // the DB mappers at this time.
        if (!($mapper instanceof \DealNews\DB\AbstractMapper)) {
            throw new \LogicException("$this->object_name does not support search");
        }

        $json_query = json_decode($this->post_data, true);

        if (!is_array($json_query)) {
            throw new \LogicException('Invalid search query');
        }

        try {
            $sq = $this->sq ?? new SearchQuery();

            $query = $sq->prepareQuery($mapper::TABLE, $mapper::PRIMARY_KEY, $json_query);
        } catch (\Throwable $e) {
            throw new \LogicException('Invalid search query: ' . $e->getMessage());
        }

        $crud = $this->crud ?? new CRUD(Factory::init($mapper::DATABASE_NAME));

        $rows = $crud->runFetch($query['query'], $query['params']);

        if (!empty($rows)) {
            $ids = [];

            foreach ($rows as $row) {
                $ids[] = $row[$mapper::PRIMARY_KEY];
            }

            $objects = $this->repository->getMulti($this->object_name, $ids);

            if (!empty($objects)) {
                foreach ($objects as $object) {
                    $data[] = $this->formatObject($object);
                }
            }
        }

        return $data;
    }
}
