[![SensioLabsInsight](https://insight.sensiolabs.com/projects/475c0c14-8534-41c1-9952-0753d6afc71a/small.png)](https://insight.sensiolabs.com/projects/475c0c14-8534-41c1-9952-0753d6afc71a)
# Translate entity

## About package
This package is intended for adding multi language support to your models. If your application already be working and you need to add one or more aditional languages to your content in database it's be easy.



## Installation

Require this package in your composer.json

    $ composer reqiure vortgo/laravel-translate

Add the service provider to you config file config/app.php

    Vortgo\Translate\ModelTranslateServiceProvider::class
Publish vendor
```
    $ php artisan vendor:publish
```
Run migration to create table for your translatable content

    $ php artisan migrate

Add trait to your model which need to translate and setup your default language for your model

```
    class Category extends Model
    {
        use Translate;
        protected $defaultLocale = 'en';
    }
```

## Usage

You can create entity with translate as usually:

```
        Category::create([
            'name' => 'name',
            'ru' => [
                'name' => 'название'
            ],
            'fr' => [
                'name' => 'fr name'
            ]
        ]);
```

For access the translate value you can use next variant:

Determine when calling
```
    $category->translate('fr')->name
```

Use your app locale
```
    App()->setLocale('fr')
    $category->name
```

Get all translations attributes for current model
```
$category->getTranslations('fr')
```

## Bonuses
You can use relations for eager loader your model
```
    App()->setLocale('fr');
    $item = Item::with('category', 'category.rTranslate')->first();
    $item->category->name;
```

To array with relation
```
    App()->setLocale('fr');
    $item = Item::with('category', 'category.rTranslate')->first();
    $item->toArray();
    Result = [
        'id' =>1,
        'item_name' => 'name',
        'category' => [
            'name' => 'fr name'
        ]
    ];
```

If you want to override function `toArray()` use `translateToArray()` in your model

```
    public function toArray()
    {
        $array = $this->translateToArray(); //parent::toArray()

        // Your code here

        return $array;
    }

```
