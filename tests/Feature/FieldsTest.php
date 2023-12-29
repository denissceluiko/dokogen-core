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
            'row__account.id', 
            'row__account.name', 
            'row__account.number',
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
    public function can_fill_tables()
    {
        $source = [
            'row__account.id', 
            'row__account.name', 
            'row__account.number',
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
    public function can_fill_all_data()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'row__account.id', 
            'row__account.name', 
            'row__account.number',
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
    public function can_flush_all_data()
    {
        $source = [
            'name', 
            'block__customer', 
            'block__customer.name', 
            'block__customer.address', 
            '/block__customer', 
            'row__account.id', 
            'row__account.name', 
            'row__account.number',
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
            'values' => [],
            'tables' => [],
            'blocks' => [],
        ];

        $fields = Fields::init($source)->fill($data);
        $fields->flush();

        $this->assertEquals($expected, $fields->toArray());
    }
}