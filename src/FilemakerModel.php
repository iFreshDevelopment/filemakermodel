<?php

namespace Ifresh\FilemakerModel;

use Ifresh\FilemakerModel\Services\Parser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;
use ReflectionClass;

class FilemakerModel
{
    const CACHE_PREFIX = 'fm_';

    private $filemaker;

    /*
     * Name of the layout where our records come from
     */
    protected $layout = '';

    /*
     * The names of fields that need to be parsed as date
     */
    protected $dates = [];

    /*
     * The names of fields that need to be parsed as integer
     */
    protected $integers = [];

    /*
     * The names of fields that need to be parsed as boolean
     */
    protected $booleans = [];

    /*
     * The names of fields that need to be parsed as float
     */
    protected $floats = [];

    /*
     * Array of filemaker fieldnames with our more logical names
     */
    protected $translate = [];

    public function __construct()
    {
        $this->filemaker = app('filemaker');
    }

    protected function parseRecords(FileMakerRelation $filemakerRecords)
    {
        return collect($filemakerRecords)->map(function ($filemakerRecord) {
            $model = new static();
            foreach ($filemakerRecord->getFieldNames() as $filemakerFieldName) {
                $fieldName = $this->getTranslatedFieldname($filemakerFieldName);

                $model->$fieldName = $this->parseValueAsType($filemakerFieldName, $filemakerRecord->$filemakerFieldName);
            }

            return $model;
        });
    }

    public function getSomeRecords(array $queryParameters)
    {
        $filemakerRecords = $this->filemaker->{$this->layout}->query($queryParameters);

        return $this->parseRecords($filemakerRecords);
    }

    protected function getAllRecords()
    {
        $filemakerRecords = Cache::get($this->getCacheKey());

        if ($filemakerRecords === null) {
            $filemakerRecordSum = 300;
            $filemakerRecords   = $this->parseRecords(
                $this->layout()->query(null, null, null, $filemakerRecordSum, null, null)
            );
            Cache::put($this->getCacheKey(), $filemakerRecords);
        }

        return $filemakerRecords;
    }

    protected function storeRecord(array $data)
    {
        return $this->layout()->create($data);
    }

    protected function parseValueAsType($fieldName, $value)
    {
        if ($value == '') {
            return null;
        }

        if (in_array($fieldName, $this->dates)) {
            return Parser::parseAsDate($value);
        }

        if (in_array($fieldName, $this->integers)) {
            return Parser::parseAsInteger($value);
        }

        if (in_array($fieldName, $this->booleans)) {
            return Parser::parseAsBoolean($value);
        }

        if (in_array($fieldName, $this->floats)) {
            return Parser::parseAsFloat($value);
        }

        return Parser::parseAsString($value);
    }

    protected function getModelName()
    {
        return (new ReflectionClass(new static()))->getShortName();
    }

    protected function getCacheKey()
    {
        return self::CACHE_PREFIX . Str::lower($this->getModelName());
    }

    public static function all()
    {
        return (new static())->getAllRecords();
    }

    public static function where(array $queryParameters)
    {
        return (new static())->getSomeRecords($queryParameters);
    }

    private function layout()
    {
        return $this->filemaker->{$this->layout};
    }

    private function getTranslatedFieldname($fieldName)
    {
        if (array_key_exists($fieldName, $this->translate)) {
            return $this->translate[$fieldName];
        }

        return $fieldName;
    }

    public static function fresh()
    {
        $static = new static();

        Cache::forget($static->getCacheKey());

        return $static;
    }

    public static function create(array $data)
    {
        return (new static())->storeRecord($data);
    }
}
