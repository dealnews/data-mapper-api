<?php

namespace DealNews\DataMapperAPI\Tests;

use \DealNews\DataMapper\Interfaces\Mapper;

class Repository extends \DealNews\DataMapper\Repository {
    protected $objs = [];

    public static function init() {
        static $inst;
        if (empty($inst)) {
            $inst = new Repository();
        }

        return $inst;
    }

    public function __construct() {
        $obj              = new TestObject();
        $obj->test_id     = 1;
        $obj->description = 'Test Object 1';
        $this->objs[1]    = $obj;
    }

    public function getMulti(string $type, array $identifiers, bool $use_cache = true): array {
        if ($type === 'TestObject') {
            $objs = [];
            foreach ($identifiers as $id) {
                if (isset($this->objs[$id])) {
                    $objs[$id] = $this->objs[$id];
                }
            }

            return $objs;
        } else {
            throw new \LogicException("There is no repository read handler for `$type`");
        }
    }

    public function save(string $type, mixed $value): mixed {
        if ($type === 'TestObject') {
            if ($value->test_id === 0) {
                $value->test_id = count($this->objs) + 1;
            }
            $this->objs[$value->test_id] = $value;

            return $value;
        } else {
            throw new \LogicException("There is no repository read handler for `$type`");
        }
    }

    public function new(string $name): object {
        if ($name === 'TestObject') {
            return new TestObject();
        } else {
            throw new \LogicException("There is no repository handler for `$name`");
        }
    }

    public function delete(string $name, $id): bool {
        if ($name === 'TestObject') {
            if (isset($this->objs[$id])) {
                unset($this->objs[$id]);
            }

            return true;
        } else {
            throw new \LogicException("There is no repository handler for `$name`");
        }
    }

    public function find(string $name, array $filters): array|bool {
        if ($name === 'TestObject') {
            if (isset($filters['test_id'])) {
                return $this->getMulti($name, [$filters['test_id']]);
            }
        } else {
            throw new \LogicException("There is no repository handler for `$name`");
        }
    }

    public function getMapper(string $name): Mapper {
        if ($name === 'TestObject') {
            return new TestObjectMapper();
        } elseif ($name === 'TestNotDBObject') {
            return new TestNotDBObjectMapper();
        } else {
            throw new \LogicException("There is no repository handler for `$name`");
        }
    }
}

class TestObjectMapper extends \DealNews\DB\AbstractMapper {
    /**
     * Database configuration name
     */
    public const DATABASE_NAME = 'test';

    /**
     * Table name
     */
    public const TABLE = 'test';

    /**
     * Table primary key column name
     */
    public const PRIMARY_KEY = 'test_id';

    public function __construct() {
        // noop
    }
}

class TestNotDBObjectMapper extends \DealNews\DataMapper\AbstractMapper {
    public function __construct() {
        // noop
    }

    public function load($id): ?object {
        return null;
    }

    public function loadMulti(array $ids): ?array {
        return null;
    }

    public function find(array $filter): ?array {
        return null;
    }

    public function save(object $object): object {
        return new stdClass();
    }

    public function delete($id): bool {
        return true;
    }

    public static function getMappedClass(): string {
        return 'TestNotDBObject';
    }
}
