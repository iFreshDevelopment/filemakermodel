<?php

namespace Ifresh\FilemakerModel;

use Exception;
use Ifresh\FilemakerModel\Services\Parser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;
use ReflectionClass;

class FilemakerModel
{
    const CACHE_PREFIX = 'fm_';

    /*
     * Name of the layout where our records come from
     */
    protected string $layout = '';

    /*
     * The names of fields that need to be parsed as date
     */
    protected array $dates = [];

    /*
     * The names of fields that need to be parsed as integer
     */
    protected array $integers = [];

    /*
     * The names of fields that need to be parsed as boolean
     */
    protected array $booleans = [];

    /*
     * The names of fields that need to be parsed as float
     */
    protected array $floats = [];

    /*
     * Array of filemaker fieldnames with our more logical names
     */
    protected array $translate = [];

    /*
     * Array of portal names containing an array of field names
     * $portals = ['portalname' => ['fieldname' => 'logical_fieldname']];
     */
    protected array $portals = [];

    private ?int $recordId;

    public function delete()
    {
        if (! $this->recordId) {
            throw new Exception('Record not loaded');
        }

        return $this->layout()->delete($this->getRecordId());
    }

    public function update($data)
    {
        if (! $this->recordId) {
            throw new Exception('Record not loaded');
        }

        // todo: work on the update method
    }

    public static function fresh()
    {
        $static = new static();

        Cache::forget($static->getCacheKey());

        return $static;
    }

    public static function find(int $recordId)
    {
        return (new static())->findRecord($recordId);
    }

    public static function findInCache(int $recordId)
    {
        return (new static())->getAllRecords($recordId)
            ->filter(fn ($record) => $record->getRecordId() === $recordId)
            ->first();
    }

    public static function create(array $data)
    {
        return (new static())->storeRecord($data);
    }

    public static function all()
    {
        return (new static())->getAllRecords();
    }

    public static function where(array $queryParameters)
    {
        return (new static())->getSomeRecords($queryParameters);
    }

    public function getRecordId()
    {
        if (! $this->recordId) {
            throw new Exception('Model is not loaded');
        }

        return (int) $this->recordId;
    }

    public static function hydrate(FileMakerRelation $records)
    {
        return (new static)->parseRecords($records);
    }

    public static function api()
    {
        $model = new static();

        return $model->layout();
    }

    private function parseRecords(FileMakerRelation $filemakerRecords)
    {
        return collect($filemakerRecords)->map(function ($filemakerRecord) {
            return $this->createModel($filemakerRecord);
        });
    }

    private function getSomeRecords(array $queryParameters)
    {
        $filemakerRecords = $this->layout()->query($queryParameters);

        return $filemakerRecords
                ? $this->parseRecords($filemakerRecords)
                : collect();
    }

    private function getAllRecords()
    {
        $filemakerRecords = Cache::get($this->getCacheKey());

        if ($filemakerRecords === null) {
            $filemakerRecordSum = 99999;
            $filemakerRecords   = $this->parseRecords(
                $this->layout()->query(null, null, null, $filemakerRecordSum, null, null)
            );
            Cache::put($this->getCacheKey(), $filemakerRecords);
        }

        return $filemakerRecords;
    }

    private function findRecord(int $recordId)
    {
        $record = $this->layout()->getRecord($recordId);

        return $this->createModel($record);
    }

    private function storeRecord(array $data)
    {
        $dataArray = [];
        foreach ($data as $fieldName => $value) {
            $filemakerFieldName = $this->getOriginalFieldName($fieldName);

            if (in_array($fieldName, $this->dates)) {
                $value = $value->format('m/d/Y');
            }

            $dataArray[$filemakerFieldName] = $value;
        }

        return $this->layout()->create($dataArray);
    }

    private function parseValueAsType($fieldName, $value)
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

    private function getModelName()
    {
        return (new ReflectionClass(new static()))->getShortName();
    }

    private function getCacheKey()
    {
        return self::CACHE_PREFIX . Str::lower($this->getModelName());
    }

    private function layout()
    {
        if (! $this->layout) {
            throw new Exception('Layout is not defined');
        }

        return app('filemaker')->{$this->layout};
    }

    private function getTranslatedFieldname($fieldName)
    {
        if (array_key_exists($fieldName, $this->translate)) {
            return $this->translate[$fieldName];
        }

        return $fieldName;
    }

    private function getOriginalFieldName($fieldName)
    {
        $originalFieldname = collect($this->translate)
            ->filter(function ($translatableFieldName) use ($fieldName) {
                return $translatableFieldName === $fieldName;
            })
            ->keys()
            ->first();

        return $originalFieldname ?? $fieldName;
    }

    private function createModel($filemakerRecord)
    {
        $model = new static();

        $model->recordId = Parser::parseAsInteger($filemakerRecord->getRecordId());

        foreach ($filemakerRecord->getFieldNames() as $filemakerFieldName) {
            $fieldName = $this->getTranslatedFieldname($filemakerFieldName);

            $model->$fieldName = $this->parseValueAsType(
                $filemakerFieldName,
                $filemakerRecord->$filemakerFieldName
            );
        }

        foreach($this->portals as $portalName => $fieldMap)
        {
            $relationObject = $filemakerRecord->field($portalName);
            $records = [];
            foreach($relationObject as $databaseRecord){
                $record = new \stdClass();
                $record->id = (int) $databaseRecord->getRecordId();
                foreach($fieldMap as $originalFieldName => $newFieldName){
                    $record->$newFieldName = $databaseRecord->$originalFieldName;
                }
                array_push($records, $record);

            }
            $model->portalData[$portalName] = $records;
        }

        return $model;
    }
}
