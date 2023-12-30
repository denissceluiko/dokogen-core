<?php

namespace Iris\Dokogen\Feature;

use Iris\Dokogen\Fields;
use Iris\Dokogen\Traits\HasTemplate;
use PHPUnit\Framework\TestCase;

/**
 * @covers Iris\Dokogen\Fields
 */
final class HasTemplateTest extends TestCase
{
    use HasTemplate;

    public Fields $bindings;

    public static ?string $fieldStorage = 'bindings';

    /**
     * @test
     */
    public function can_init_field_storage_variable()
    {
        $this->assertEquals('bindings', $this->getFieldStorageKey());
        self::$fieldStorage = null;
        $this->assertEquals('template_fields', $this->getFieldStorageKey());
        self::$fieldStorage = 'bindings';
    }
    
    /**
     * @test
     */
    public function can_retrieve_values()
    {
        $this->bindings = Fields::init()->fillValues([
            "key" => "value",
        ]);

        $this->assertEquals(['key' => 'value'], $this->fields()->values());
    }

   
}