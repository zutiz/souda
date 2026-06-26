<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Model::clearBootedModels();

        $reflection = new ReflectionClass(Model::class);
        $bootingProperty = $reflection->getProperty('booting');
        $bootingProperty->setAccessible(true);
        $bootingProperty->setValue(null, []);
    }
}
