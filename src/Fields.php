<?php

namespace Iris\Dokogen;

class Fields
{
    protected array $keys = [];
    protected array $values = [];
    protected array $tableGroups = [];
    protected array $tableGroupValues = [];
    protected array $blockGroups = [];
    protected array $blockGroupValues = [];

    public function __construct(Fields|array $variables = null)
    {
        if ($variables instanceof Fields) {
            $this->fromSibling($variables);
        } elseif (is_array($variables) && $this->isFormatted($variables)) {
            $this->fromArray($variables);
        } elseif (is_array($variables)) {
            $this->extract($variables);
        }
    }

    public static function init(Fields|array $variables = null) : static
    {
        return new static($variables);
    }

    /**
     * Expected contents of the input $variables is the output of 
     * PhpOffice\PhpWord\TemplateProcessor::getVariables()
     *
     * @param array $variables
     * @return void
     */
    protected function extract(array $variables) : void
    {
        $tableMacros = $this->locateMacros('table', $variables);
        $variables = $this->removeMacros($variables, $tableMacros);
        $this->setTables($this->groupTableMacros($tableMacros));

        $blockMacros = $this->locateMacros('block', $variables);
        $variables = $this->removeMacros($variables, $blockMacros);
        $this->setBlocks($this->groupBlockMacros($blockMacros));

        $this->setKeys($variables);
        $this->fillValues(array_fill_keys($variables, null));
    }

    protected function fromArray(array $source) : void
    {
        $this->setTables($source['tables']);
        $this->setBlocks($source['blocks']);
        $this->setKeys(array_keys($source['values']));
    }

    protected function fromSibling(Fields $source) : void
    {
        $this->setTables($source->tableGroups);
        $this->setBlocks($source->blockGroups);
        $this->setKeys($source->keys);
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
            $this->fillFormatted($data->toArray());
        } elseif ($this->isFormatted($data)) {
            $this->fillFormatted($data);
        }

        foreach ($data as $k => $v) {
            // Table or block
            if (is_array($v)) {
                $this->fillTable($k, $v);
                $this->fillBlock($k, $v);
            }

            $this->fillValues([$k => $v]);
        }


        return $this;
    }

    protected function fillFormatted(array $data)
    {
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
    }

    public function fillValues(array $values) : self
    {
        foreach ($values as $k => $v) {
            if (!in_array($k, $this->keys)) continue;

            $this->values[$k] = $v;
        }

        return $this;
    }

    public function fillTable(string $name, array $values) : self
    {
        // Skip if table doesn't exist
        if (!isset($this->tableGroups[$name])) return $this;

        if (!array_is_list($values)) {
            $values = [$values];
        }

        foreach ($values as $entry) {
            $newRow = [];

            foreach ($entry as $column => $value) {
                if (!in_array($column, $this->tableGroups[$name])) continue;
                
                $newRow[$column] = $value;
            }
            
            $this->tableGroupValues[$name][] = $newRow;
        }       
        
        return $this;
    }

    public function fillBlock(string $name, array $values) : self
    {
        // Skip if block doesn't exist
        if (!isset($this->blockGroups[$name])) return $this;

        if (!array_is_list($values)) {
            $values = [$values];
        }

        foreach ($values as $entry) {
            $newCopy = [];

            foreach ($entry as $field => $value) {
                if (!in_array($field, $this->blockGroups[$name])) continue;
                
                $newCopy[$field] = $value;
            }
            
            $this->blockGroupValues[$name][] = $newCopy;
        }  

        return $this;
    }

    protected function setKeys(array $keys) : self
    {
        // Drop pre-existing values
        if (!array_is_list($keys)) {
            $keys = array_keys($keys);
        }

        $this->keys = $keys;
        return $this;
    }

    /**
     * Expects a map of tables
     *
     * @param array $tables
     * @return self
     */
    protected function setTables(array $tables) : self
    {
        $this->tableGroups = [];

        foreach ($tables as $name => $columns) {
            // Drop pre-existing values
            if (!array_is_list($columns)) {
                $columns = array_keys($columns);
            }

            $this->tableGroups[$name] = $columns;
        }
        
        return $this;
    }
    
    protected function setBlocks(array $blocks) : self
    {
        $this->blockGroups = [];

        foreach ($blocks as $name => $fields) {
            // Drop pre-existing values
            if (!array_is_list($fields)) {
                $fields = array_keys($fields);
            }

            $this->blockGroups[$name] = $fields;
        }

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

    public function tables(bool $fullPath = false) : array
    {
        return $fullPath ? $this->tablesForCloning() : $this->tableGroupValues;
    }

    protected function tablesforCloning() : array
    {
        $tables = [];

        foreach ($this->tableGroupValues as $table => $rows) {
            foreach ($rows as $index => $columns) {
                $tables[$table][$index] = self::prefixArrayKeys($columns, 'table__'.$table.'.');
            }
        }

        return $tables;
    }
    
    public function blocks(bool $fullPath = false) : array
    {
        return $fullPath ? $this->blocksforCloning() : $this->blockGroupValues;
    }

    protected function blocksforCloning() : array
    {
        $blocks = [];

        foreach ($this->blockGroupValues as $block => $groups) {
            foreach ($groups as $index => $fields) {
                $blocks[$block][$index] = self::prefixArrayKeys($fields, 'block__'.$block.'.');
            }
        }

        return $blocks;
    }

    public function tableIdFor(string $table) : ?string
    {
        if (!isset($this->tableGroups[$table])) return null;

        return 'table__'.$table.'.'.$this->tableGroups[$table][0];
    }

    public function blockIdFor(string $block) : ?string
    {
        if (!isset($this->blockGroups[$block])) return null;

        return 'block__'.$block;
    }

    /**
     * Returns an array containing names of all the fillable values, 
     * tables and blocks.
     *
     * @return array
     */
    public function names() : array
    {
        return [
            'blocks'    => $this->blockGroups,
            'tables'    => $this->tableGroups,
            'values'    => $this->keys,
        ];
    }

    /**
     * Returns all the filled values with their corresponding keys.
     *
     * @return array
     */
    public function toArray() : array
    {
        return [
            'blocks'    => $this->blockGroupValues,
            'tables'    => $this->tableGroupValues,
            'values'    => $this->values,
        ];
    }

    /**
     * Returns a blank fields object containing all the keys
     * of the current fields and all their values set to null.
     * 
     * @return void
     */
    public function blank() : array
    {
        return [
            'blocks'    => array_map(fn($block) => array_fill_keys($block, null), $this->blockGroups),
            'tables'    => array_map(fn($table) => array_fill_keys($table, null), $this->tableGroups),
            'values'    => array_fill_keys($this->keys, null),
        ];
    }

    public function isFormatted(array $data)
    {
        return isset($data['blocks']) && is_array($data['blocks'])
            && isset($data['tables']) && is_array($data['tables'])
            && isset($data['values']) && is_array($data['values']);
    }

    public static function prefixArrayKeys(array $array, string $prefix) {
        return array_combine(
            array_map(fn($key) => $prefix . $key, array_keys($array)),
            $array
        );
    }
}