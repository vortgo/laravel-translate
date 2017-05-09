<?php

namespace Vortgo\Translate\Tests;

use Orchestra\Testbench\TestCase;
use Vortgo\Translate\Exceptions\Translate\DeleteTranslateException;
use Vortgo\Translate\Exceptions\Translate\SaveTranslateException;
use Vortgo\Translate\Tests\Models\Category;

class TranslateTraitTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/../src/database/migrations'),
        ]);

        $this->loadMigrationsFrom([
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__ . '/migrations'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Vortgo\Translate\ModelTranslateServiceProvider::class,
            'Orchestra\Database\ConsoleServiceProvider'
        ];
    }

    /**
     * Test create entity and translate from array
     *
     * @param array $entityData
     * @param array $expected
     *
     * @dataProvider createEntityWithTranslateDataProvider
     */
    public function testCreateEntityWithTranslate(array $entityData, array $expected)
    {
        config(['translate.allowedLanguages' => ['en', 'ru', 'fr']]);
        $category = Category::create($entityData);

        foreach ($expected as $lang => $value) {
            $this->assertEquals($value, $category->translate($lang)->name);
        }
    }

    /**
     * Return value on default language if some lang not allowed in app
     *
     * @param array $entityData
     * @param array $expected
     * @dataProvider langNotAllowedDataProvider
     */
    public function testLangNotAllowed(array $entityData, array $expected)
    {
        config(['translate.allowedLanguages' => ['en']]);
        $category = Category::create($entityData);

        foreach ($expected as $lang => $value) {
            $this->assertEquals($value, $category->translate($lang)->name);
        }
    }

    /**
     * Test auto change variable locale in changes app locale
     *
     * @param array $entityData
     * @param $expected
     * @dataProvider changeAppLocaleDataProvider
     */
    public function testChangeAppLocale(array $entityData, $expected)
    {
        $category = Category::create($entityData);
        $this->assertEquals($entityData['name'], $category->name);
        app()->setLocale('ru');
        $this->assertEquals($expected, $category->name);
    }

    /**
     * Test return default value if translated value not found
     *
     * @param array $entityData
     * @dataProvider returnDefaultValueDataProvider
     */
    public function testReturnDefaultValue(array $entityData)
    {
        $category = Category::create($entityData);
        $this->assertEquals($entityData['name'], $category->name);
        $this->assertEquals($entityData['name'], $category->translate('ru')->name);
        app()->setLocale('ru');
        $this->assertEquals($entityData['name'], $category->name);
    }

    /**
     * Test manual add translation to entity
     *
     * @param array $entityData
     * @param array $translate
     * @dataProvider manualAddTranslateDataProvider
     */
    public function testManualAddTranslate(array $entityData, array $translate)
    {
        $category = Category::create($entityData);
        $category->saveTranslation($translate['locale'], $translate['key'], $translate['value']);

        $key = $translate['key'];

        $this->assertEquals($translate['value'], $category->translate($translate['locale'])->$key);
        app()->setLocale($translate['locale']);
        $this->assertEquals($translate['value'], $category->$key);
    }

    /**
     * Test add translate to not exist model attribute
     *
     * @param array $entityData
     * @param array $translate
     * @dataProvider failManualAddTranslateDataProvider
     */
    public function testFailManualAddTranslate(array $entityData, array $translate)
    {
        $this->expectException(SaveTranslateException::class);

        $category = Category::create($entityData);
        $category->saveTranslation($translate['locale'], $translate['key'], $translate['value']);
    }

    /**
     * Test has translation entity
     *
     * @param array $entityData
     * @param $expected
     * @dataProvider hasTranslateDataProvider
     */
    public function testHasTranslate(array $entityData, $expected)
    {
        $category = Category::create($entityData);
        $this->assertEquals($expected, $category->hasTranslation());
    }

    /**
     * Test get translated value in toArray function of entity
     *
     * @param array $entityData
     * @param array $expected
     * @dataProvider toArrayDataProvider
     */
    public function testToArray(array $entityData, array $expected)
    {
        $category = Category::create($entityData);
        app()->setLocale($expected['lang']);
        $arrayOfEntity = $category->toArray();
        $this->assertEquals($expected['value'], $arrayOfEntity['name']);
    }

    /**
     * Test get all translation from entity
     *
     * @param array $entityData
     * @param array $expected
     * @dataProvider getTranslationsDataProvider
     */
    public function testGetTranslations(array $entityData, array $expected)
    {
        config(['translate.allowedLanguages' => ['en', 'ru', 'fr']]);
        $category = Category::create($entityData);
        $this->assertEquals($expected['translate_data'], $category->getTranslations($expected['locale']));
    }

    /**
     * Test update entity and translate
     *
     * @param array $entityData
     * @param array $updateData
     * @param array $expected
     * @dataProvider updateDataProvider
     */
    public function testUpdate(array $entityData, array $updateData, array $expected)
    {
        $category = Category::create($entityData);
        $category->update($updateData);

        $key = $expected['key'];
        $this->assertEquals($expected['value'], $category->translate($expected['lang'])->$key);
    }

    /**
     * Test delete translation from entity
     *
     * @param array $entityData
     * @param array $delete
     * @dataProvider deleteTranslationDataProvider
     */
    public function testDeleteTranslation(array $entityData, array $delete)
    {
        $category = Category::create($entityData);
        $this->assertTrue($category->hasTranslation());
        $category->deleteTranslation($delete['locale'], $delete['key']);
        $this->assertFalse($category->hasTranslation());
    }

    /**
     * Failed save translate
     */
    public function testFailSaveTranslation()
    {
        $this->expectException(SaveTranslateException::class);
        $category = new Category();
        $category->saveTranslation(str_random(), str_random(), str_random());
    }

    /**
     * @return array
     */
    public function createEntityWithTranslateDataProvider()
    {
        return [
            'Default value and ru translate'          => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ]
                ],
                [
                    'ru' => 'название',
                ]
            ],
            'Default value with ru and fr translates' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                    'fr'   => [
                        'name' => 'fr name'
                    ]
                ],
                [
                    'ru' => 'название',
                    'fr' => 'fr name'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function langNotAllowedDataProvider()
    {
        return [
            'Ru and fr not allowed' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                    'fr'   => [
                        'name' => 'fr name'
                    ]
                ],
                [
                    'ru' => 'name',
                    'fr' => 'name'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function changeAppLocaleDataProvider()
    {
        return [
            'Ru translate' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                'название'
            ],
        ];
    }

    /**
     * @return array
     */
    public function returnDefaultValueDataProvider()
    {
        return [
            'Default - name' => [
                [
                    'name' => 'name',
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function manualAddTranslateDataProvider()
    {
        return [
            'Manual add ru' => [
                [
                    'name' => 'name',
                ],
                [
                    'locale' => 'ru',
                    'key'    => 'name',
                    'value'  => 'название'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function failManualAddTranslateDataProvider()
    {
        return [
            'Name not exist in model' => [
                [
                    'name' => 'name',
                ],
                [
                    'locale' => 'ru',
                    'key'    => 'title',
                    'value'  => 'название'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function hasTranslateDataProvider()
    {
        return [
            'Name must be exist'       => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                true
            ],
            'Name don t must be exist' => [
                [
                    'name' => 'name',
                ],
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function toArrayDataProvider()
    {
        return [
            'Ru translate to array' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                [
                    'lang'  => 'ru',
                    'value' => 'название'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function getTranslationsDataProvider()
    {
        return [
            'Ru data translation'         => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                [
                    'locale'         => 'ru',
                    'translate_data' => [
                        'ru' => [
                            'name' => 'название'
                        ],
                    ]
                ]
            ],
            'Fr Data translation'         => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                    'fr'   => [
                        'name' => 'fr name'
                    ],
                ],
                [
                    'locale'         => 'fr',
                    'translate_data' => [
                        'fr' => [
                            'name' => 'fr name'
                        ],
                    ]
                ]
            ],
            'Fr and ru data translations' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                    'fr'   => [
                        'name' => 'fr name'
                    ],
                ],
                [
                    'locale'         => null,
                    'translate_data' => [
                        'ru' => [
                            'name' => 'название'
                        ],
                        'fr' => [
                            'name' => 'fr name'
                        ],
                    ]
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return [
            'Update ru translation' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                [
                    'name' => 'new name',
                    'ru'   => [
                        'name' => 'новое название'
                    ],
                ],
                [
                    'lang'  => 'ru',
                    'key'   => 'name',
                    'value' => 'новое название'
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function deleteTranslationDataProvider()
    {
        return [
            'Delete ru translations' => [
                [
                    'name' => 'name',
                    'ru'   => [
                        'name' => 'название'
                    ],
                ],
                [
                    'locale' => 'ru',
                    'key'    => 'name'
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function failDeleteTranslationDataProvider()
    {
        return [
            'Delete ru translations' => [
                [
                    'locale' => 'ru',
                    'key'    => 'name'
                ]
            ]
        ];
    }
}