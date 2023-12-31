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

    public function flush() : self
    {
        $this->fields->flush();
        return $this;
    }

    /**
     * Populates a string with values from stored value fields.
     * Ignores tables and blocks.
     */
    public function populate(string $string) : string
    {
        $result = $string;
        preg_match_all('/\$\{(.*?)}/i', $string, $matches);

        $values = $this->fields->values();

        for ($i=0; $i<count($matches[0]); $i++)
        {
            $result = str_replace($matches[0][$i], $values[$matches[1][$i]] ?? $matches[0][$i], $result);
        }

        return $result;
    }

    public function compile() : TemplateProcessor
    {
        $processor = new TemplateProcessor($this->templatePath);
        $processor->setValues($this->fields->values());

        foreach ($this->fields->tables(fullPath: true) as $table => $values)
        {
            $identifier = $this->fields->tableIdFor($table);
            $processor->cloneRowAndSetValues($identifier, $values);
        }

        // TBD: further investiogation; doesn't work properly.
        foreach ($this->fields->blocks(fullPath: true) as $block => $values)
        {
            $identifier = $this->fields->blockIdFor($block);
            $processor->cloneBlock($identifier, 0, true, false, $values);
        }

        return $processor;
    }

    /**
     * Batch compile documents
     *
     * @param array $data
     * @return array
     */
    public function batch(array $data) : array
    {
        if (!array_is_list($data)) return [];

        $processors = [];

        foreach ($data as $document) {
            $processors[] = $this->flush()->fill($document)->compile();
        }

        return $processors;
    }

    public function hash($algo = 'sha256') : ?string
    {
        return hash_file($algo, $this->templatePath);
    }

    public function getFields() : Fields
    {
        return $this->fields;
    }
}