<?php

namespace Iris\Dokogen\Feature;

use Iris\Dokogen\Fields;
use Iris\Dokogen\Template;
use PHPUnit\Framework\TestCase;

/**
 * @covers Iris\Dokogen\Template
 */
final class TemplateTest extends TestCase
{
    protected string $stubsDir = __DIR__.'/../stubs';
    
    /**
     * @test
     */
    function can_locate_bindings()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
        $fields = $template->getFields()->names();

        $this->assertEquals([
            'values' => ['name'],
            'rows' => [
                'account' => [
                    'id', 'name', 'number'
                ],
            ],
            'blocks' => [
                'customer' => [
                    'name', 'address'
                ],
            ],
        ], $fields);
    }

    /**
     * @test
     */
    function can_compile_a_document()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $data = $template->getFields()->fillValues([
            'name' => 'John',
        ]);

        $processor = $template->fill($data)->compile();
        $path = 'can_compile_a_document.docx';
        $processor->saveAs($path);
        $this->assertFileExists($path);
        unlink($path);
    }

    /**
     * @test
     */
    function can_populate_a_string()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $data = $template->getFields()->fillValues([
            'name' => 'John',
            'surname' => 'Wick',
        ]);

        $string = $template->fill($data)->populate('${name} ${surname}.docx');
        $this->assertEquals('John Wick.docx', $string);
    }
    
}