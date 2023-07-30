<?php

namespace Iris\Dokogen;

class Fields
{
    protected array $values = [];
    protected array $rowGroups = [];
    protected array $rowGroupValues = [];
    protected array $blockGroups = [];
    protected array $blockGroupValues = [];

    public function __construct(array $variables = null)
    {
        if (is_array($variables)) {
            $this->extract($variables);
        }
    }

    public static function init(array $variables = null) : static
    {
        return new static($variables);
    }

    protected function extract(array $variables) : void
    {
        $rowMacros = $this->locateMacros('row', $variables);
        $variables = $this->removeMacros($variables, $rowMacros);
        $this->setRows($this->groupRowMacros($rowMacros));

        $blockMacros = $this->locateMacros('block', $variables);
        $variables = $this->removeMacros($variables, $blockMacros);
        $this->setBlocks($this->groupBlockMacros($blockMacros));

        $this->fillValues(array_fill_keys($variables, null));
    }

    protected function locateMacros($type, array $macros) : array
    {
        $rows = preg_grep("/{$type}__(.*)\.?(.*)/i", $macros);
        return array_values($rows);
    }

    protected function removeMacros(array $bindings, array $macros) : array
    {
        return array_values(array_filter($bindings, function($binding) use ($macros) {
            return !in_array($binding, $macros);
        }));
    }

    /**
     * Groups row macros
     *
     * @param array $macros
     * @return array
     */
    protected function groupRowMacros(array $macros) : array
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            // Remove the 'row__' prefix
            $macro = substr($macro, strlen('row__'));

            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][$cell] = null;
            } else {
                // Row macro has at least one element, the one initializing it.
                $groups[$macro][$macro] = null;
            }
        }
        return $groups;
    }

    /**
     * Groups block macros
     *
     * @param array $macros
     * @return array
     */
    protected function groupBlockMacros(array $macros) : array
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            // Catch closing macro
            $macro = ltrim($macro, '/');

            // Remove the 'block__' prefix
            $macro = substr($macro, strlen('block__'));

            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][$cell] = null;
            } elseif(!isset($groups[$macro])) {
                // Block macro can be empty inside
                $groups[$macro] = [];
            }
        }
        return $groups;
    }

    public function fill(Fields|array $data) : self
    {
        if ($data instanceof Fields) {
            $data = $data->toArray();
        }

        if (isset($data['values'])) {
            $this->fillValues($data['values']);
        }

        if (isset($data['blocks'])) {
            foreach ($data['blocks'] as $block => $values) {
                $this->fillBlock($block, $values);
            }
        }

        if (isset($data['rows'])) {
            foreach ($data['rows'] as $table => $values) {
                $this->fillTable($table, $values);
            }
        }

        return $this;
    }

    public function fillValues(array $values) : self
    {
        $this->values = $values;
        return $this;
    }

    public function fillTable(string $name, array $values) : self
    {
        // Skip if table doesn't exist
        if (!isset($this->rowGroups[$name])) return $this;

        if (!array_is_list($values)) {
            $values = [$values];
        }

        $this->rowGroupValues[$name] = $values;
        return $this;
    }

    public function fillBlock(string $name, array $values) : self
    {
        // Skip if block doesn't exist
        if (!isset($this->blockGroups[$name])) return $this;

        if (!array_is_list($values)) {
            $values = [$values];
        }

        $this->blockGroupValues[$name] = $values;
        return $this;
    }

    protected function setRows(array $rows) : self
    {
        $this->rowGroups = $rows;
        return $this;
    }
    
    protected function setBlocks(array $blocks) : self
    {
        $this->blockGroups = $blocks;
        return $this;
    }

    public function values() : array
    {
        return $this->values;
    }

    public function tables() : array
    {
        return $this->rowGroupValues;
    }
    
    public function blocks() : array
    {
        return $this->blockGroupValues;
    }

    public function names() : array
    {
        return [
            'blocks'    => array_map('array_keys', $this->blockGroups),
            'rows'      => array_map('array_keys', $this->rowGroups),
            'values'    => array_keys($this->values),
        ];
    }

    public function toArray() : array
    {
        return [
            'blocks'    => $this->blockGroupValues,
            'rows'      => $this->rowGroupValues,
            'values'    => $this->values,
        ];
    }
}