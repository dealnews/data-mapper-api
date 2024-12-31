<?php

namespace DealNews\DataMapperAPI\Action;

/**
 * Gets a single object
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class GetObject extends Base {

    /**
     * Type of object to delete
     *
     * @var string
     */
    protected string $object_name;

    /**
     * Id of object to delete
     *
     * @var int
     */
    protected int $object_id;

    /**
     * Loads data
     *
     * @return     array
     */
    public function loadData(): array {
        $data    = [];

        if ($this->object_id == 0) {
            $data = $this->repository->new($this->object_name);
        } else {
            $object = $this->repository->get(
                $this->object_name,
                $this->object_id
            );

            if (!empty($object)) {
                $data = $object;
            } else {
                $data['http_status'] = 404;
                $data['error']       = 'Not Found';
            }
        }

        if (is_object($data)) {
            $data = $this->formatObject($data);
        }

        return $data;
    }
}
