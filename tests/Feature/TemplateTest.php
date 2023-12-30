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
        ]);

        $string = $template->fill($data)->populate('${name}.docx');
        $this->assertEquals('John.docx', $string);
    }

    /**
     * @test
     */
    function will_not_populate_nonexistent_keys()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $data = $template->getFields()->fillValues([
            'name' => 'John',
            'surname' => 'Wick',
        ]);

        $string = $template->fill($data)->populate('${name} ${surname}.docx');
        $this->assertEquals('John ${surname}.docx', $string);
    }

    /**
     * @test
     */
    function can_compute_a_hash()
    {
        $template = Template::load($this->stubsDir.'/basic_template.docx');
     
        $hash = $template->hash();

        $this->assertEquals('01aa275bdec8d54b2dcccdf3035a2e11465a52980ae07b965a663b8552d5b1df', $hash);
    }
    
}