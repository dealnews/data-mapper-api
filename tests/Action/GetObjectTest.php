<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\GetObject;

class GetObjectTest extends TestCase {
    public function testFound() {
        $obj  = new GetObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'object_id'   => 1,
            ]
        );

        $this->assertEquals(
            [
                'test_id'     => 1,
                'description' => 'Test Object 1',
                'http_status' => 200,
            ],
            $data
        );
    }

    public function testNotFound() {
        $obj  = new GetObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'object_id'   => 2,
            ]
        );

        $this->assertEquals(
            [
                'error'       => 'Not Found',
                'http_status' => 404,
            ],
            $data
        );
    }

    public function testBadObject() {
        $obj  = new GetObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'BadObject',
                'object_id'   => 2,
            ]
        );

        $this->assertEquals(
            [
                'error'       => 'There is no repository read handler for `BadObject`',
                'http_status' => 400,
            ],
            $data
        );
    }
}
