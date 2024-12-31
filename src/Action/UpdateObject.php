<?php

namespace DealNews\DataMapperAPI\Action;

/**
 * Updates/Saves an object
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class UpdateObject extends Base {

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
    protected int $object_id = 0;

    /**
     * POST data in raw format
     *
     * @var string
     */
    protected string $post_data;

    /**
     * Loads data
     *
     * @return     array
     */
    public function loadData(): array {
        $data = [];

        if (!empty($this->post_data)) {
            if ($this->object_id > 0) {
                $object = $this->repository->get($this->object_name, $this->object_id);
                if (empty($object)) {
                    throw new \LogicException("Object $this->object_name with id $this->object_id does not exist");
                }
            } else {
                $object = $this->repository->new($this->object_name);
            }

            if (!isset($object)) {
                throw new \LogicException("Unknown object $this->object_name");
            }

            $object_data = json_decode($this->post_data, true);

            $object_data = $this->validateData($object, $object_data);

            if (empty($object_data)) {
                throw new \LogicException("No valid data provided for $this->object_name");
            }

            try {
                $object->fromArray($object_data);
            } catch (\Throwable $e) { // @phan-suppress-current-line PhanUnusedVariableCaughtException
                throw new \LogicException("Invalid data for $this->object_name");
            }
        }

        if (isset($object)) {
            $object = $this->repository->save(
                $this->object_name,
                $object
            );

            if (!empty($object)) {
                $data = $this->formatObject($object);
                if ($this->object_id > 0) {
                    $data['http_status'] = 200;
                } else {
                    $data['http_status'] = 201;
                }
            } else {
                $data['http_status'] = 500;
            }
        }

        return $data;
    }

    /**
     * Validates the data in the array applies to the given object
     *
     * @param      object           $object       The object
     * @param      array            $object_data  The object data
     *
     * @throws     \LogicException
     *
     * @return     array
     */
    protected function validateData(object $object, array $object_data): array {
        $props       = get_class_vars(get_class($object));
        $usable_data = [];

        foreach ($object_data as $prop => $value) {
            if (array_key_exists($prop, $props)) {
                $usable_data[$prop] = $value;
            } else {
                throw new \LogicException("Invalid property $prop for $this->object_name");
            }
        }

        return $usable_data;
    }
}
