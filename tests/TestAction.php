<?php

namespace DealNews\DataMapperAPI\Tests;

class TestAction extends \DealNews\DataMapperAPI\Action\Base {

    public static array $_mock_data = [];

    public static $current_obj;

    public string $base_url;

    public function __invoke(array $inputs, \DealNews\DataMapper\Repository $repository, bool $throw = false) {
        parent::__invoke($inputs, $repository, $throw);
        self::$current_obj = $this;
    }

    public function loadData(): array {
        return self::$_mock_data;
    }

    public function respond(array $data) {
        // noop
    }
}
