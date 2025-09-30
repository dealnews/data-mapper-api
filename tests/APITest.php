<?php

namespace DealNews\DataMapperAPI\Tests;

use \DealNews\DataMapperAPI\API;

class APITest extends \PHPUnit\Framework\TestCase {

    public function testExecuteAction() {

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $api = new API();
        $api->executeAction(TestAction::class, [], 'https://www.example1.com', Repository::init());

        $action = TestAction::$current_obj;

        $this->assertEquals($action->base_url, 'https://www.example1.com');

        putenv('DN_SERVER_NAME=www.example2.com');

        $api = new API();
        $api->executeAction(TestAction::class, [], '', Repository::init());

        $action = TestAction::$current_obj;

        $this->assertEquals($action->base_url, 'https://www.example2.com');

        unset($_SERVER['REQUEST_METHOD']);
        putenv('DN_SERVER_NAME=');

    }

    public function testGetAllRoutes() {
        $api = new API();

        $routes = $api->getAllRoutes();

        $expect = [
            'type'    => 'starts_with',
            'pattern' => '/api/',
            'routes'  => [
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/(\\d+)/$!',
                    'method'  => 'GET',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\GetObject',
                    'tokens'  => [
                        'object_name',
                        'object_id',
                    ],
                ],
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/$!',
                    'method'  => 'GET',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\GetObjects',
                    'tokens'  => [
                        'object_name',
                    ],
                ],
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/_search/$!',
                    'method'  => 'POST',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\SearchObjects',
                    'tokens'  => [
                        'object_name',
                    ],
                ],
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/(\\d+)/$!',
                    'method'  => 'PUT',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\UpdateObject',
                    'tokens'  => [
                        'object_name',
                        'object_id',
                    ],
                ],
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/$!',
                    'method'  => 'POST',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\UpdateObject',
                    'tokens'  => [
                        'object_name',
                    ],
                ],
                [
                    'type'    => 'regex',
                    'pattern' => '!^/api/([^/]+)/(\\d+)/$!',
                    'method'  => 'DELETE',
                    'action'  => '\\DealNews\\DataMapperAPI\\Action\\DeleteObject',
                    'tokens'  => [
                        'object_name',
                        'object_id',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expect, $routes);
    }
}
