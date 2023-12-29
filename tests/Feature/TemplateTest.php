<?php

namespace Iris\Dokogen\Feature;

use Iris\Dokogen\Fields;
use Iris\Dokogen\Template;
use PhpOffice\PhpWord\TemplateProcessor;
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
            'tables' => [
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
    function can_compile_a_document_batch()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $data = [
            [
                'values' => [
                    'name' => 'John',
                ],
            ],
            [
                'values' => [
                    'name' => 'Jane',
                ],
            ],
            [
                'values' => [
                    'name' => 'Jim',
                ],
            ],        
        ];

        $processors = $template->batch($data);
        $this->assertCount(3, $processors);
        $this->assertContainsOnlyInstancesOf(TemplateProcessor::class, $processors);
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

    /**
     * @test
     */
    function can_compute_a_hash()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $hash = $template->hash();

        $this->assertEquals('249bb90e383fef2028ba16671c7bbc559fdbe14e4b1003cdd60f1c03d6b7e14a', $hash);
    }
    
}