<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\DeleteObject;

class DeleteObjectTest extends TestCase {
    public function testFound() {
        $obj  = new DeleteObject();
        $data = $this->invoke(
            $obj,
            [
                'object_name' => 'TestObject',
                'object_id'   => 2,
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
