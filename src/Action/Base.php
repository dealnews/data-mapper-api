<?php

namespace DealNews\DataMapperAPI\Action;

use DealNews\DataMapper\Repository;
use Moonspot\ValueObjects\Interfaces\Export;

/**
 * Base API Class
 *
 * @author      Brian Moon <brianm@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
abstract class Base {
    /**
     * Repository
     *
     * @var \DealNews\DataMapper\Repository
     */
    protected Repository $repository;

    /**
     * The base url of the API
     *
     * Typically, this property comes in via the __invoke $inputs param via the API::executeAction() method
     *
     * @var string
     */
    protected string $base_url;

    public function __invoke(array $inputs, Repository $repository, bool $throw = false) {
        foreach ($inputs as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->repository = $repository;

        try {
            $data = $this->loadData();
            if (empty($data['http_status'])) {
                // assume everything is good
                // if there was no exception and
                // the loadData function did not set
                // and http status
                $data['http_status'] = 200;
            }
        } catch (\LogicException $e) {
            if ($throw) {
                throw $e;
            }
            // this is thrown when the object name
            // is not registered with the Repository
            // or invalid data is passed for an object
            $data['http_status'] = 400;
            $data['error']       = $e->getMessage();
        }

        $this->respond($data);
    }

    /**
     * Standard JSON response
     *
     * @param      array  $data   The data
     */
    public function respond(array $data) {
        if (!empty($data['http_status'])) {
            if ($data['http_status'] >= 400) {
                $error = $data['error'] ?? 'Unknown Error Occured';
                throw new \RuntimeException($error, $data['http_status']);
            }

            if (!empty($_SERVER['REQUEST_URI'])) {
                http_response_code($data['http_status']);
                unset($data['http_status']);
            }
        } elseif (empty($data)) {
            throw new \RuntimeException('Not Found', 404);
        }

        echo json_encode($data);
    }

    /**
     * Ensures objects are formatted as an array for response data
     *
     * @param      Export        $data   The data
     *
     * @return     array
     */
    protected function formatObject(Export $data): array {
        $data = $data->toArray();

        return $data;
    }

    /**
     * Data loader for child class to implement
     *
     * @return     array
     */
    abstract public function loadData(): array;
}
