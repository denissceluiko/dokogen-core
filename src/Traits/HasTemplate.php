<?php 

namespace Iris\Dokogen\Traits;

use Iris\Dokogen\Fields;

trait HasTemplate
{
    protected ?Fields $__fields = null;

    public function fields(): Fields
    {
        if (is_null($this->__fields)) {
            $this->__fields = Fields::init($this->{$this->getFieldStorageKey()});
        }

        return $this->__fields;
    }

    public function getFieldStorageKey() : string
    {
        return $this->fieldStorage ?? 'fields';
    }
}