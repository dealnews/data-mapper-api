<?php

namespace DealNews\DataMapperAPI\Action;

/**
 * Deletes an object
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class DeleteObject extends Base {

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
        $data = [];

        $success = $this->repository->delete(
            $this->object_name,
            $this->object_id
        );

        if (!$success) {
            $data['http_status'] = 500;
        } else {
            $data['http_status'] = 200;
        }

        return $data;
    }
}
