<?php

namespace DealNews\DataMapperAPI\Action;

use \DealNews\DataMapper\Repository;

/**
 * Gets multiple objects
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class GetObjects extends Base {

    /**
     * Type of object to delete
     *
     * @var string
     */
    protected string $object_name;

    protected array $filter = [];

    public function __invoke(array $inputs, Repository $repository, bool $throw = false) {
        $this->filter = $inputs;
        unset(
            $this->filter['object_name'],
            $this->filter['base_url']
        );
        parent::__invoke($inputs, $repository, $throw);
    }

    /**
     * Loads data
     *
     * @return     array
     */
    public function loadData(): array {
        $data    = [];

        $filter = [];

        $new_object = $this->repository->new($this->object_name);

        foreach ($this->filter as $prop => $value) {
            if (property_exists($new_object, $prop)) {
                $filter[$prop] = $value;
            }
        }

        $objects = $this->repository->find(
            $this->object_name,
            $filter
        );

        if (!empty($objects)) {
            $data = array_values($objects);
            foreach ($data as $key => $object) {
                if (is_object($object)) {
                    $data[$key] = $this->formatObject($object);
                }
            }
        }

        return $data;
    }
}
