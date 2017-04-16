<?php

namespace Vortgo\Translate\Traits;

use Vortgo\Translate\Exceptions\Translate\DeleteTranslateException;
use Vortgo\Translate\Exceptions\Translate\SaveTranslateException;
use Vortgo\Translate\Models\Translation;

trait Translate
{
    /** @var  string */
    private $selectedLocale;

    public function rTranslate()
    {

        return $this->hasMany('\Vortgo\Translate\Models\Translation', 'entity_id', $this->primaryKey)->where('entity_name', $this->getMorphClass());
    }

    /**
     * Selected locale
     *
     * @param $lang
     * @return $this
     */
    public function translate($lang)
    {
        $this->selectedLocale = $lang;
        return $this;
    }

    /**
     *  Dynamically retrieve attributes on the model with locale
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            $locale = $this->getLocaleForTranslate();
            if ($locale) {
                if ($value = $this->getTranslatedValue($locale, $key)) {
                    $this->selectedLocale = null;
                    return $value;
                }
            }
        }

        return $this->getAttribute($key);
    }

    /**
     * Check have translation for model
     *
     * @param null $locale
     * @return bool
     */
    public function hasTranslation($locale = null)
    {
        if ($locale == $this->defaultLocale) {
            return true;
        }
        if ($locale) {
            return (bool)$this->rTranslate->where('locale', $locale)->count();
        } else {
            return (bool)$this->rTranslate->count();
        }
    }

    /**
     * Get all translation for model
     *
     * @param null $locale
     * @return array
     */
    public function getTranslations($locale = null)
    {
        $result = [];
        if ($locale) {
            $translates = $this->rTranslate->where('locale', $locale)->all();
        } else {
            $translates = $this->rTranslate;
        }
        foreach ($translates as $translate) {
            $result[$translate->locale][] = [$translate->entity_attribute => $translate->value];
        }

        return $result;
    }

    /**
     * Save translatio=-0 for model attribute
     *
     * @param $locale
     * @param $key
     * @param $value
     * @throws SaveTranslateException
     */
    public function saveTranslation($locale, $key, $value)
    {
        if (!array_key_exists($key, $this->attributes) && !$this->hasGetMutator($key)) {
            throw new SaveTranslateException("{$key} not found in model attribute");
        }

        if ($locale == $this->defaultLocale) {
            throw new SaveTranslateException('This locale of data is default locale of model');
        }

        if (!$translation = $this->getTranslationModelEntity($locale, $key)) {
            if (!$translation = $this->rTranslate()->where('locale', $locale)->where('entity_attribute', $key)->first()) {
                try {
                    Translation::create([
                        'entity_id' => $this->{$this->primaryKey},
                        'entity_name' => $this->getMorphClass(),
                        'locale' => $locale,
                        'entity_attribute' => $key,
                        'value' => $value
                    ]);
                } catch (\Exception $e) {
                    throw new SaveTranslateException("Can't save new translation for model", 0, $e);
                }
            }
        } else {
            try {
                $translation->update([
                    'value' => $value
                ]);
            } catch (\Exception $e) {
                throw new SaveTranslateException("Can't save translation for model", 0, $e);
            }
        }
    }

    /**
     * Delete translation
     *
     * @param $locale
     * @param $key
     * @throws DeleteTranslateException
     */
    public function deleteTranslation($locale, $key)
    {
        try {
            $this->rTranslate()->where('locale', $locale)->where('entity_attribute', $key)->delete();
        } catch (\Exception $e) {
            throw new DeleteTranslateException('Can\'t delete translation for model', 0, $e);
        }
    }

    /**
     * Redefinition toArray function
     *
     * @return mixed
     */
    public function translateToArray()
    {
        $array = parent::toArray();
        $locale = $this->getLocaleForTranslate();
        if ($locale) {
            foreach (array_keys($array) as $key) {
                $translated = $this->getTranslatedValue($locale, $key);
                if ($translated) {
                    $array[$key] = $translated;
                }
            }
        }
        return $array;
    }

    /**
     * Redefinition toArray function
     *
     * @return mixed
     */
    public function toArray()
    {
        return $this->translateToArray();
    }


    /**
     * Create model with translation
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        $model = new static($attributes);

        $model->save();

        $model->saveTranslationFromArray($attributes, $model);

        return $model;
    }

    /**
     * Update model with translation
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (!$this->exists) {
            return false;
        }

        $this->saveTranslationFromArray($attributes, $this);

        return $this->fill($attributes)->save($options);
    }

    /**
     * Get value from translate
     *
     * @param $locale
     * @param $key
     * @return mixed|null
     */
    private function getTranslatedValue($locale, $key)
    {
        /** @var Translation $translation */
        if ($translation = $this->getTranslationModelEntity($locale, $key)) {
            return $translation->value;
        }
        return null;
    }

    /**
     * Get translation entity
     *
     * @param $locale
     * @param $key
     * @return Translation|null
     */
    private function getTranslationModelEntity($locale, $key)
    {
        /** @var Translation $translation */
        if ($translation = $this->rTranslate()->where('locale', $locale)->where('entity_attribute', $key)->first()) {
            return $translation;
        }
        return null;
    }

    /**
     * Get locale based on default and selected properties
     *
     * @return null|string
     */
    private function getLocaleForTranslate()
    {
        $locale = null;
        if ($this->selectedLocale && $this->selectedLocale != $this->defaultLocale) {
            $locale = $this->selectedLocale;
        } elseif (app()->getLocale() != $this->defaultLocale) {
            $locale = app()->getLocale();
        }
        return $locale;
    }

    /**
     * Save translations from array
     *
     * @param array $attributes
     * @param $model
     */
    private function saveTranslationFromArray(array $attributes, $model)
    {
        try {
            $allowLanguages = config('translate.allowLanguages');
            foreach ($attributes as $locale => $array) {
                if (is_array($array) && in_array($locale,$allowLanguages)) {
                    foreach ($array as $key => $value) {
                        if (isset($key) && isset($value) && $key && $value) {
                            $model->saveTranslation($locale, $key, $value);
                        }
                    }
                }
            }
        } catch (SaveTranslateException $e) {
            \Log::error("try to save translate for {$model->getMorphClass()}");
        }
    }
}

