<?php

namespace Vortgo\Translate\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Vortgo\Translate\Tests\Models\Category;

class TranslateTraitTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        Schema::create('categories', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('translations', function( $table)
        {
            $table->bigInteger('translation_id', true)->unsigned();
            $table->bigInteger('entity_id')->unsigned()->index('translations_entity_id_idx');
            $table->string('entity_name')->index('translations_entity_name_idx');
            $table->string('entity_attribute');
            $table->string('locale', 5)->index('translations_locale_idx');
            $table->text('value', 65535);
            $table->timestamps();
        });
    }

    public function tearDown()
    {
        Schema::drop('categories');
        Schema::drop('translations');
        parent::tearDown();
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
            //'Cartalyst\Sentry\SentryServiceProvider',
            //'YourProject\YourPackage\YourPackageServiceProvider',
        ];
    }

    /**
     * Get package aliases.  In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file.  If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            //'Sentry'      => 'Cartalyst\Sentry\Facades\Laravel\Sentry',
            //'YourPackage' => 'YourProject\YourPackage\Facades\YourPackage',
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
        $category = Category::create($entityData);

        $this->assertEquals($expected['value'],$category->translate($expected['lang'])->name);
    }

    /**
     * @return array
     */
    public function createEntityWithTranslateDataProvider()
    {
        return [
            [
                [
                    'name' => 'name',
                    'ru' => [
                        'name' => 'название'
                    ]
                ],
                [
                    'lang' => 'ru',
                    'value' => 'название'
                ]
            ]
        ];
    }

}