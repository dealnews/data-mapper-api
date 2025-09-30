<?php

namespace DealNews\DataMapperAPI\Tests\Action;

use \DealNews\DataMapperAPI\Action\Base;
use \DealNews\DataMapperAPI\Tests\Repository;

abstract class TestCase extends \PHPUnit\Framework\TestCase {
    protected function invoke(Base $obj, array $input, bool $throw = false): array {
        $data = [];
        ob_start();
        try {
            $obj($input, Repository::init(), $throw);
            $json = ob_get_clean();
            $data = json_decode($json, true);
        } catch (\Throwable $e) {
            if ($throw) {
                throw $e;
            }
            ob_end_clean();
            $data = [
                'http_status' => $e->getCode(),
                'error'       => $e->getMessage(),
            ];
        }

        return $data;
    }
}
