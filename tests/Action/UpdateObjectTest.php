<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\UpdateObject;

class UpdateObjectTest extends TestCase {
    public function testNew() {
        $obj  = new UpdateObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => json_encode([
                    'description' => 'Test Object 999',
                ]),

            ]
        );

        $this->assertNotEmpty($data['test_id']);

        $this->assertEquals(
            [
                'test_id'     => $data['test_id'],
                'description' => 'Test Object 999',
                'http_status' => 201,
            ],
            $data
        );

        return $data['test_id'];
    }

    /**
     * @depends testNew
     */
    public function testExisting($id) {
        $obj  = new UpdateObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'object_id'   => $id,
                'post_data'   => json_encode([
                    'test_id'     => $id,
                    'description' => 'Test Object updated',
                ]),

            ]
        );

        $this->assertEquals(
            [
                'test_id'     => $id,
                'description' => 'Test Object updated',
                'http_status' => 200,
            ],
            $data
        );
    }

    public function testBadData() {
        $obj  = new UpdateObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => json_encode([
                    'test_id'  => 1,
                    'bad_data' => 'Test Object updated',
                ]),

            ]
        );

        $this->assertEquals(
            [
                'error'       => 'Invalid property bad_data for TestObject',
                'http_status' => 400,
            ],
            $data
        );
    }

    public function testEmptyData() {
        $obj  = new UpdateObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'post_data'   => json_encode([]),

            ]
        );

        $this->assertEquals(
            [
                'error'       => 'No valid data provided for TestObject',
                'http_status' => 400,
            ],
            $data
        );
    }

    public function testExistingNotFound() {
        $obj  = new UpdateObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'object_id'   => 999,
                'post_data'   => json_encode([
                    'test_id'     => 999,
                    'description' => 'Test Object updated',
                ]),

            ]
        );

        $this->assertEquals(
            [
                'error'       => 'Object TestObject with id 999 does not exist',
                'http_status' => 400,
            ],
            $data
        );
    }
}
