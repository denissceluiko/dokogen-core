<?php 

namespace Iris\Dokogen\Traits;

use Iris\Dokogen\Fields;
use Iris\Dokogen\Template;
use PhpOffice\PhpWord\TemplateProcessor;

trait HasTemplate
{
    protected ?Fields $__fields = null;
    protected ?Template $__template = null;

    /**
     * Separate from Template::$fields
     * No need to load a template if we don't intend to compile.
     *
     * @return Fields
     */
    public function fields(): Fields
    {
        if (is_null($this->__fields)) {
            $this->__fields = Fields::init($this->{$this->getFieldStorageKey()});
        }

        return $this->__fields;
    }

    public function template() : Template
    {
        if (is_null($this->__template)) {
            $this->__template = Template::load($this->getTemplatePath());
        }

        return $this->__template;
    }

    public function compile() : TemplateProcessor
    {
        return $this->template()->fill($this->__fields)->compile();
    }

    public function getFieldStorageKey() : string
    {
        return self::$fieldStorage ?? 'template_fields';
    }
    
    abstract public function getTemplatePath() : string;
}