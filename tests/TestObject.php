<?php

namespace DealNews\DataMapperAPI\Tests;

class TestObject extends \Moonspot\ValueObjects\ValueObject {
    public const UNIQUE_ID_FIELD = 'test_id';

    public int $test_id = 0;

    public string $description = '';
}
