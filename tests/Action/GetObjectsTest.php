<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\GetObjects;

class GetObjectsTest extends TestCase {
    public function testFound() {
        $obj  = new GetObjects();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'test_id'     => 1,
            ]
        );

        $this->assertEquals(
            [
                [
                    'test_id'     => 1,
                    'description' => 'Test Object 1',
                ],
                'http_status' => 200,
            ],
            $data
        );
    }

    public function testNotFound() {
        $obj  = new GetObjects();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'test_id'     => 2,
            ]
        );

        $this->assertEquals(
            [
                'http_status' => 200,
            ],
            $data
        );
    }
}
