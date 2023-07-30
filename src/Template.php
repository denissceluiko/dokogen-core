<?php

namespace Iris\Dokogen;

use PhpOffice\PhpWord\TemplateProcessor;

class Template
{
    protected Fields $fields;
    protected string $templatePath;

    public function __construct(string $path)
    {
        $this->templatePath = $path;
        $processor = new TemplateProcessor($this->templatePath);
        $this->fields = Fields::init($processor->getVariables());
    }

    public static function load(string $path) : self
    {
        return new static($path);
    }

    public function fill(Fields|array $data) : self
    {
        $this->fields->fill($data);
        return $this;
    }

    /**
     * Populates a string with values from stored value fields.
     * Ignores rows and blocks.
     */
    public function populate(string $string) : string
    {
        $result = $string;
        preg_match_all('/\$\{(.*?)}/i', $string, $matches);

        $values = $this->fields->values();

        for ($i=0; $i<count($matches[0]); $i++)
        {
            $result = str_replace($matches[0][$i], $values[$matches[1][$i]], $result);
        }

        return $result;
    }

    public function compile() : TemplateProcessor
    {
        $processor = new TemplateProcessor($this->templatePath);
        $processor->setValues($this->fields->values());

        foreach ($this->fields->tables() as $table => $values)
        {
            $processor->cloneRowAndSetValues($table, $values);
        }

        foreach ($this->fields->blocks() as $block => $values)
        {
            $processor->cloneBlock($block, 0, true, false, $values);
        }

        return $processor;
    }

    public function getFields() : Fields
    {
        return $this->fields;
    }
}