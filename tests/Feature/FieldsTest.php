<?php

namespace Iris\Dokogen\Feature;

use Iris\Dokogen\Fields;
use PHPUnit\Framework\TestCase;

/**
 * @covers Iris\Dokogen\Fields
 */
final class FieldsTest extends TestCase
{
    /**
     * @test
     */
    public function can_init_fields()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $expected = [
            'values' => ['name'],
            'tables' => [
                'account' => ['id', 'name', 'number'],
            ],
            'blocks' => [
                'customer' => ['name', 'address'],
            ],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->names());
    }

    /**
     * @test
     */
    public function can_init_from_one_field()
    {
        $source = [
            'name', 
        ];

        $expected = [
            'values' => ['name'],
            'tables' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->names());
    }

    /**
     * @test
     */
    public function can_fill_values()
    {
        $source = [
            'name',
        ];

        $values = [
            'name' => 'John',
        ];

        $expected = [
            'values' => ['name' => 'John'],
            'tables' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source)->fillValues($values);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function will_not_fill_nonexistent_keys()
    {
        $source = [
            'name',
        ];

        $values = [
            'name' => 'John',
            'surname' => 'Wick',
        ];

        $expected = [
            'values' => ['name' => 'John'],
            'tables' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source)->fillValues($values);

        $this->assertEquals($expected, $fields->toArray());
    }
    
    /**
     * @test
     */
    public function can_init_tables()
    {
        $source = [
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $expected = [
            'tables' => [
                'account' => [
                    [
                        'id' => null, 
                        'name' => null, 
                        'number' => null,
                    ],
                ],
            ],
            'values' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function can_fill_tables()
    {
        $source = [
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'id' => 1, 
            'name' => 'Jane', 
            'number' => 123,
        ];

        $expected = [
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ],
                ],
            ],
            'values' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source)->fillTable('account', $data);
        $fields->fillTable('account2', $data);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function will_not_fill_nonexistent_fields_in_tables()
    {
        $source = [
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'id' => 1, 
            'name' => 'Jane', 
            'number' => 123,
            'type' => 'user',
        ];

        $expected = [
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ],
                ],
            ],
            'values' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source)->fillTable('account', $data);
        $fields->fillTable('account2', $data);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function can_init_blocks()
    {
        $source = [
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
        ];

        $expected = [
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
            'values' => [],
            'tables' => [],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function can_fill_blocks()
    {
        $source = [
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
        ];

        $data = [
            'name' => 'Jim',
            'address' => 'Shork st 4',
        ];

        $expected = [
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
            'values' => [],
            'tables' => [],
        ];

        $fields = Fields::init($source)->fillBlock('customer', $data);
        $fields->fillBlock('customer2', $data);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function will_not_fill_nonexistent_fields_in_blocks()
    {
        $source = [
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
        ];

        $data = [
            'name' => 'Jim',
            'surname' => 'Beam',
            'address' => 'Shork st 4',
        ];

        $expected = [
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
            'values' => [],
            'tables' => [],
        ];

        $fields = Fields::init($source)->fillBlock('customer', $data);
        $fields->fillBlock('customer2', $data);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function can_fill_all_data()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'values' => [
                'name' => 'John',
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ]
                ],
            ],
        ];

        $expected = [
            'values' => ['name' => 'John'],
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source)->fill($data);

        $this->assertEquals($expected, $fields->toArray());
    }
    
    /**
     * @test
     */
    public function can_fill_ungrouped_data()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'name' => 'John',
            'customer' => [
                [
                    'name' => 'Jim',
                    'address' => 'Shork st 4',
                ]
            ],
            'account' => [
                [
                    'id' => 1, 
                    'name' => 'Jane', 
                    'number' => 123,
                ]
            ],
        ];

        $expected = [
            'values' => ['name' => 'John'],
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source)->fill($data);

        $this->assertEquals($expected, $fields->toArray());
    }

    /**
     * @test
     */
    public function can_flush_all_data()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'values' => [
                'name' => 'John',
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => 'Jim',
                        'address' => 'Shork st 4',
                    ]
                ],
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => 1, 
                        'name' => 'Jane', 
                        'number' => 123,
                    ]
                ],
            ],
        ];

        $expected = [
            'values' => [
                'name' => null,
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => null, 
                        'name' => null, 
                        'number' => null,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source)->fill($data);
        $fields->flush();

        $this->assertEquals($expected, $fields->toArray());
    }
    
    /**
     * @test
     */
    public function can_produce_a_blank()
    {
        $source = [
            'name', 
            'title', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $expected = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => null,
                        'name' => null,
                        'number' => null,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->blank());
    }

    /**
     * @test
     */
    public function can_init_from_array()
    {
        $source = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    'id' => null,
                    'name' => null,
                    'number' => null,
                ],
            ],
            'blocks' => [
                'customer' => [
                    'name' => null,
                    'address' => null,
                ],
            ],
        ];

        $expected = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => null,
                        'name' => null,
                        'number' => null,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source);

        $this->assertEquals($expected, $fields->blank());
    }

    
    /**
     * @test
     */
    public function can_init_from_names()
    {
        $source = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    'id' => null,
                    'name' => null,
                    'number' => null,
                ],
            ],
            'blocks' => [
                'customer' => [
                    'name' => null,
                    'address' => null,
                ],
            ],
        ];

        $expected = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => null,
                        'name' => null,
                        'number' => null,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
        ];

        $fields = Fields::init($source);
        $copy = Fields::init($fields->names());

        $this->assertEquals($expected, $copy->toArray());
    }

    /**
     * @test
     */
    public function can_init_from_sibling()
    {
        $source = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    'id' => null,
                    'name' => null,
                    'number' => null,
                ],
            ],
            'blocks' => [
                'customer' => [
                    'name' => null,
                    'address' => null,
                ],
            ],
        ];

        $expected = [
            'values' => [
                'name' => null,
                'title' => null,
            ],
            'tables' => [
                'account' => [
                    [
                        'id' => null,
                        'name' => null,
                        'number' => null,
                    ]
                ],
            ],
            'blocks' => [
                'customer' => [
                    [
                        'name' => null,
                        'address' => null,
                    ]
                ],
            ],
        ];

        $original = Fields::init($source);
        $copy = Fields::init($original);

        $this->assertEquals($expected, $copy->blank());
    }

    /**
     * @test
     */
    public function can_export_tables()
    {
        $source = [
            'table__account.id', 
            'table__account.name', 
            'table__account.number',
        ];

        $data = [
            'id' => 1, 
            'name' => 'Jane', 
            'number' => 123,
        ];

        $expected = [
            'account' => [
                [
                    'table__account.id' => 1, 
                    'table__account.name' => 'Jane', 
                    'table__account.number' => 123,
                ],
            ],
        ];

        $fields = Fields::init($source)->fillTable('account', $data);
        $fields->fillTable('account2', $data);

        $this->assertEquals($expected, $fields->tables(fullPath: true));
    }

    /**
     * @test
     */
    public function can_export_blocks()
    {
        $source = [
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
        ];

        $data = [
            'name' => 'Jim',
            'address' => 'Shork st 4',
        ];

        $expected = [
            'customer' => [
                [
                    'block__customer.name' => 'Jim',
                    'block__customer.address' => 'Shork st 4',
                ]
            ],
        ];

        $fields = Fields::init($source)->fillBlock('customer', $data);
        $fields->fillBlock('customer2', $data);

        $this->assertEquals($expected, $fields->blocks(fullPath: true));
    }
}