<?php namespace Elifbyte\AdminGenerator\Generate\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait Helpers
{

    public function option($key = null)
    {
        return ($key === null || $this->hasOption($key)) ? parent::option($key) : null;
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * Determine if the file already exists.
     *
     * @param $path
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->files->exists($path);
    }


    /**
     * Check if provided relation has a table
     *
     * @param $relationTable
     * @return mixed
     */
    public function checkRelationTable($relationTable)
    {
        return Schema::hasTable($relationTable);
    }

    /**
     * sets Relation of Belongs To Many type
     *
     * @param $belongsToMany
     * @return mixed
     */
    //TODO add other relation types
    public function setBelongToManyRelation($belongsToMany)
    {
        $this->relations['belongsToMany'] = collect(explode(',', $belongsToMany))->filter(function ($belongToManyRelation) {
            return $this->checkRelationTable($belongToManyRelation);
        })->map(function ($belongsToMany) {
            return [
                'current_table' => $this->tableName,
                'related_table' => Str::singular($belongsToMany),
                'related_model' => ($belongsToMany == 'roles') ? "Spatie\\Permission\\Models\\Role" : $this->qualifyClass(Str::studly(Str::singular($belongsToMany)), $this->moduleName),
                'related_model_class' => ($belongsToMany == 'roles') ? "Spatie\\Permission\\Models\\Role::class" : $this->qualifyClass(Str::studly(Str::singular($belongsToMany)) . '::class', $this->moduleName),
                'related_model_name' => Str::studly(Str::singular($belongsToMany)),
                'related_model_name_plural' => Str::studly($belongsToMany),
                'related_model_variable_name' => lcfirst(Str::singular(class_basename($belongsToMany))),
                'relation_table' => trim(collect([$this->tableName, $belongsToMany])->sortBy(function ($table) {
                    return $table;
                })->reduce(function ($relationTable, $table) {
                    return $relationTable . '_' . $table;
                }), '_'),
                'foreign_key' => Str::singular($this->tableName) . '_id',
                'related_key' => Str::singular($belongsToMany) . '_id',
            ];
        })->keyBy('related_table');
    }

    /**
     * sets Relation of Belongs To type
     *
     * @param $belongsTo
     * @return mixed
     */
    public function setBelongToRelation($belongsTo)
    {
        $this->relations['belongsTo'] = collect(explode(',', $belongsTo))->filter(function ($belongToRelation) {
            return $this->checkRelationTable($belongToRelation);
        })->map(function ($belongsTo) {
            return [
                'current_table' => $this->tableName,
                'related_table' => Str::singular($belongsTo),
                'related_model' => $this->qualifyClass(Str::studly(Str::singular($belongsTo)), $this->moduleName),
                'related_model_class' => $this->qualifyClass(Str::studly(Str::singular($belongsTo)) . '::class', $this->moduleName),
                'related_model_name' => Str::studly(Str::singular($belongsTo)),
                'related_model_name_plural' => Str::studly($belongsTo),
                'related_model_variable_name' => lcfirst(Str::singular(class_basename($belongsTo))),
                'foreign_key' => Str::singular($belongsTo) . '_id',
                'owner_key' => 'id',
            ];
        })->keyBy('related_table');
    }

    /**
     * sets Relation of Has Many or Has One
     *
     * @param $belongsTo
     * @return mixed
     */
    public function setHasRelation($has, $type)
    {
        $hasRelation = 'has'.$type;

        $this->relations[$hasRelation] = collect(explode(',', $has))->filter(function ($hasRelation) {
            return $this->checkRelationTable($hasRelation);
        })->map(function ($hasRelation) {
            return [
                'current_table' => $this->tableName,
                'related_table' => Str::singular($hasRelation),
                'related_model' => $this->qualifyClass(Str::studly(Str::singular($hasRelation)), $this->moduleName),
                'related_model_class' => $this->qualifyClass(Str::studly(Str::singular($hasRelation)) . '::class', $this->moduleName),
                'related_model_name' => Str::studly(Str::singular($hasRelation)),
                'related_model_name_plural' => Str::studly($hasRelation),
                'related_model_variable_name' => lcfirst(Str::singular(class_basename($hasRelation))),
                'foreign_key' => Str::singular($hasRelation) . '_id',
                'local_key' => 'id',
            ];
        })->keyBy('related_table');
    }


    /**
     * Determine if the content is already present in the file
     *
     * @param $path
     * @param $content
     * @return bool
     */
    protected function alreadyAppended($path, $content)
    {
        if (strpos($this->files->get($path), $content) !== false) {
            return true;
        }
        return false;
    }

}
