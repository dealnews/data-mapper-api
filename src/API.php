<?php

namespace DealNews\DataMapperAPI;

/**
 * Interface for invoking the DataMapper API from a router
 *
 * @author      Jeremy Earle <jearle@dealnews.com>
 * @copyright   1997-Present DealNews.com, Inc
 * @package     \DealNews\DataMapperAPI
 */
class API {

    /**
     * Default route prefix for API calls
     *
     * @var        string
     */
    const DEFAULT_ROUTE_PREFIX = '/api';

    /**
     * PageMill Router config to the endpoint to retrieve a single object by name and id
     *
     * @var array
     */
    protected array $get_object_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/(\d+)/',
        'method'  => 'GET',
        'action'  => "\DealNews\DataMapperAPI\Action\GetObject",
        'tokens'  => [
            'object_name',
            'object_id',
        ],
    ];

    /**
     * PageMill Router config to the endpoint to retrieve a multiple objects by name
     *
     * @var array
     */
    protected array $get_objects_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/',
        'method'  => 'GET',
        'action'  => "\DealNews\DataMapperAPI\Action\GetObjects",
        'tokens'  => [
            'object_name',
        ],
    ];

    /**
     * PageMill Router config to the endpoint to search multiple objects by name
     *
     * @var array
     */
    protected array $search_objects_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/_search/',
        'method'  => 'POST',
        'action'  => "\DealNews\DataMapperAPI\Action\SearchObjects",
        'tokens'  => [
            'object_name',
        ],
    ];

    /**
     * PageMill Router config to the endpoint to update an already existing object
     *
     * @var array
     */
    protected array $update_object_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/(\d+)/',
        'method'  => 'PUT',
        'action'  => "\DealNews\DataMapperAPI\Action\UpdateObject",
        'tokens'  => [
            'object_name',
            'object_id',
        ],
    ];

    /**
     * PageMill Router config to the endpoint to create a new object
     *
     * @var array
     */
    protected array $create_object_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/',
        'method'  => 'POST',
        'action'  => "\DealNews\DataMapperAPI\Action\UpdateObject",
        'tokens'  => [
            'object_name',
        ],
    ];

    /**
     * PageMill Router config to the endpoint to delete an existing object
     *
     * @var array
     */
    protected array $delete_object_route = [
        'type'    => 'regex',
        'pattern' => '/([^/]+)/(\d+)/',
        'method'  => 'DELETE',
        'action'  => "\DealNews\DataMapperAPI\Action\DeleteObject",
        'tokens'  => [
            'object_name',
            'object_id',
        ],
    ];

    /**
     * List of routes that will be exported when calling getAllRoutes()
     *
     * @var string[]
     */
    protected array $routes_list = [
        'get_object_route',
        'get_objects_route',
        'search_objects_route',
        'update_object_route',
        'create_object_route',
        'delete_object_route',
    ];

    /**
     * Takes the full classname of an action, a list of tokens, the base url, and a repository object. Then executes the
     * action with the provided data formatted in the correct manner.
     *
     * @param   string                              $action         The full class name of the action to execute (including namespace)
     * @param   array                               $tokens         A list of tokens extracted from the url of this endpoint
     * @param   string                              $base_url       The base url to pass to the action
     * @param   \DealNews\DataMapper\Repository     $repository     A data mapper repository object to pass to the action
     */
    public function executeAction(string $action, array $tokens, string $base_url, \DealNews\DataMapper\Repository $repository) {
        $object = new $action();

        $tokens = array_merge($_GET, $tokens);

        if (empty($base_url) && !empty(getenv('DN_SERVER_NAME'))) {
            $base_url = 'https://' . getenv('DN_SERVER_NAME');
        }

        if (empty($base_url)) {
            throw new \LogicException("base_url is not set", 500);
        }

        $tokens['base_url'] = $base_url;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT') {
            $tokens['post_data'] = file_get_contents('php://input');
        }

        $object($tokens, $repository);
    }

    /**
     * Retrieve a single route by name and apply a route prefix to its route pattern
     *
     * @param   string      $route_name     The route name
     * @param   string      $route_prefix   The prefix to apply to the route pattern (should start with a '/', but should NOT end in one)
     *
     * @return  array|null                  The route if successful, null otherwise
     */
    public function getRoute(string $route_name, string $route_prefix = API::DEFAULT_ROUTE_PREFIX) : ?array {
        $route = null;
        if (property_exists($this, $route_name)) {
            $route = $this->$route_name;
            if (!empty($route['pattern'])) {
                $route['pattern'] = '!^' . $route_prefix . $route['pattern'] . '$!';
            }
        }

        return $route;
    }

    /**
     * Retrieves all known routes for this API library and applies a route prefix to each route pattern
     *
     * @param   string      $route_prefix   The prefix to apply to the route pattern (should start with a '/', but should NOT end in one)
     *
     * @return  array                       An array to add to list of routes to provide to PageMill Router
     */
    public function getAllRoutes(string $route_prefix = API::DEFAULT_ROUTE_PREFIX) : array {
        $allroutes = [
            'type'    => 'starts_with',
            'pattern' => $route_prefix . '/',
            'routes'  => [],
        ];

        foreach ($this->routes_list as $route_name) {
            $route = $this->getRoute($route_name, $route_prefix);
            if (!empty($route)) {
                $allroutes['routes'][] = $route;
            }
        }

        return $allroutes;
    }
}
