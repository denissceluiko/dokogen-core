<?php

namespace Iris\Dokogen\Feature;

use Iris\Dokogen\Traits\HasTemplate;
use PhpOffice\PhpWord\TemplateProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers Iris\Dokogen\Fields
 */
final class HasTemplateTest extends TestCase
{
    use HasTemplate;

    public array $bindings;

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
        $this->bindings = ['key'];
        
        $this->fields()->fillValues([
            "key" => "value",
        ]);


        $this->assertEquals(['key' => 'value'], $this->fields()->values());
    }

    public function getTemplatePath(): string
    {
        return __DIR__.'/../stubs/basic_template.docx';
    }

    /**
     * @test
     */
    public function can_compile_document()
    {
        $this->bindings = ['key'];
        
        $this->fields()->fill([
            'name' => 'Jeremy',
        ]);

        $result = $this->compile();


        $this->assertTrue($result instanceof TemplateProcessor);
    }

}