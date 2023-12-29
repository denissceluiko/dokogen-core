<?php

namespace Iris\Dokogen;

class Fields
{
    protected array $values = [];
    protected array $tableGroups = [];
    protected array $tableGroupValues = [];
    protected array $blockGroups = [];
    protected array $blockGroupValues = [];

    public function __construct(Fields|array $variables = null)
    {
        if ($variables instanceof Fields) {
            $this->fill($variables);
        } elseif (is_array($variables) && $this->isFormatted($variables)) {
            $this->fill($variables);
        } elseif (is_array($variables)) {
            $this->extract($variables);
        }
    }

    public static function init(Fields|array $variables = null) : static
    {
        return new static($variables);
    }

    protected function extract(array $variables) : void
    {
        $tableMacros = $this->locateMacros('table', $variables);
        $variables = $this->removeMacros($variables, $tableMacros);
        $this->setTables($this->groupTableMacros($tableMacros));

        $blockMacros = $this->locateMacros('block', $variables);
        $variables = $this->removeMacros($variables, $blockMacros);
        $this->setBlocks($this->groupBlockMacros($blockMacros));

        $this->fillValues(array_fill_keys($variables, null));
    }

    protected function locateMacros($type, array $macros) : array
    {
        $macros = preg_grep("/{$type}__(.*)\.?(.*)/i", $macros);
        return array_values($macros);
    }

    protected function removeMacros(array $bindings, array $macros) : array
    {
        return array_values(array_filter($bindings, function($binding) use ($macros) {
            return !in_array($binding, $macros);
        }));
    }

    /**
     * Groups table macros
     *
     * @param array $macros
     * @return array
     */
    protected function groupTableMacros(array $macros) : array
    {
        $groups = [];
        foreach ($macros as $macro)
        {
            // Remove the 'table__' prefix
            $macro = substr($macro, strlen('table__'));

            if (strpos($macro, '.')) {
                list($macro, $cell) = explode('.', $macro);
                $groups[$macro][$cell] = null;
            } else {
                // Table macro has at least one element, the one initializing it.
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

        if (isset($data['tables'])) {
            foreach ($data['tables'] as $table => $values) {
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
        if (!isset($this->tableGroups[$name])) return $this;

        if (!array_is_list($values)) {
            $values = [$values];
        }

        $this->tableGroupValues[$name] = $values;
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

    protected function setTables(array $tables) : self
    {
        $this->tableGroups = $tables;
        return $this;
    }
    
    protected function setBlocks(array $blocks) : self
    {
        $this->blockGroups = $blocks;
        return $this;
    }

    public function flush() : self
    {
        $this->values = [];
        $this->tableGroupValues = [];
        $this->blockGroupValues = [];

        return $this;
    }

    public function values() : array
    {
        return $this->values;
    }

    public function tables() : array
    {
        return $this->tableGroupValues;
    }
    
    public function blocks() : array
    {
        return $this->blockGroupValues;
    }

    public function names() : array
    {
        return [
            'blocks'    => array_map('array_keys', $this->blockGroups),
            'tables'      => array_map('array_keys', $this->tableGroups),
            'values'    => array_keys($this->values),
        ];
    }

    public function toArray() : array
    {
        return [
            'blocks'    => $this->blockGroupValues,
            'tables'      => $this->tableGroupValues,
            'values'    => $this->values,
        ];
    }

    public function isFormatted(array $data)
    {
        return isset($data['blocks']) && is_array($data['blocks'])
            && isset($data['tables']) && is_array($data['tables'])
            && isset($data['values']) && is_array($data['values']);
    }
}