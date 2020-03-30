<?php namespace Nicolasc\AdminGenerator\Generate;

use Nicolasc\AdminGenerator\Generate\Traits\FileManipulations;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ViewForm extends ViewGenerator
{

    use FileManipulations;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'admin-module:generate:form';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate create and edit view templates';

    /**
     * Path for create view
     *
     * @var string
     */
    protected $create = 'create';

    /**
     * Path for edit view
     *
     * @var string
     */
    protected $edit = 'edit';

    /**
     * Path for form view
     *
     * @var string
     */
    protected $form = 'form';

    /**
     * Path for form right view
     *
     * @var string
     */
    protected $formRight = 'form-right';

    /**
     * Path for js view
     *
     * @var string
     */
    protected $formJs = 'form-js';

    protected $translatable = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $force = $this->option('force');
        $translatable = $this->option('translatable');

        //TODO check if exists
        //TODO make global for all generator
        //TODO also with prefix
        if (!empty($template = $this->option('template'))) {
            $this->create = 'templates.' . $template . '.create';
            $this->edit = 'templates.' . $template . '.edit';
            $this->form = 'templates.' . $template . '.form';
            $this->formRight = 'templates.' . $template . 'form-right';
            $this->formJs = 'templates.' . $template . '.form-js';
        }

        if (!empty($belongsToMany = $this->option('belongs-to-many'))) {
            $this->setBelongToManyRelation($belongsToMany);
        }

        $viewPath = module_path($this->moduleName, 'Resources/views/admin/' . $this->modelViewsDirectory . '/components/form-elements.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildForm());

            $this->info('Generating ' . $viewPath . ' finished');
        }

        if (in_array("published_at", array_column($this->getVisibleColumns($this->tableName, $this->modelVariableName)->toArray(), 'name'))) {
            $viewPath = module_path($this->moduleName, 'Resources/views/admin/' . $this->modelViewsDirectory . '/components/form-elements-right.blade.php');
            if ($this->alreadyExists($viewPath) && !$force) {
                $this->error('File ' . $viewPath . ' already exists!');
            } else {
                if ($this->alreadyExists($viewPath) && $force) {
                    $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                    $this->files->delete($viewPath);
                }

                $this->makeDirectory($viewPath);

                $this->files->put($viewPath, $this->buildFormRight());

                $this->info('Generating ' . $viewPath . ' finished');
            }
        }

        $viewPath = module_path($this->moduleName, 'Resources/views/admin/' . $this->modelViewsDirectory . '/create.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildCreate());

            $this->info('Generating ' . $viewPath . ' finished');
        }


        $viewPath = module_path($this->moduleName, 'Resources/views/admin/' . $this->modelViewsDirectory . '/edit.blade.php');
        if ($this->alreadyExists($viewPath) && !$force) {
            $this->error('File ' . $viewPath . ' already exists!');
        } else {
            if ($this->alreadyExists($viewPath) && $force) {
                $this->warn('File ' . $viewPath . ' already exists! File will be deleted.');
                $this->files->delete($viewPath);
            }

            $this->makeDirectory($viewPath);

            $this->files->put($viewPath, $this->buildEdit());

            $this->info('Generating ' . $viewPath . ' finished');
        }

        $formJsPath = module_path($this->moduleName, 'Resources/assets/js/admin/' . $this->modelJSName . '/Form.js');

        if ($this->alreadyExists($formJsPath) && !$force) {
            $this->error('File ' . $formJsPath . ' already exists!');
        } else {
            if ($this->alreadyExists($formJsPath) && $force) {
                $this->warn('File ' . $formJsPath . ' already exists! File will be deleted.');
                $this->files->delete($formJsPath);
            }

            $this->makeDirectory($formJsPath);

            $this->files->put($formJsPath, $this->buildFormJs());
            $this->info('Generating ' . $formJsPath . ' finished');
        }

        $indexJsPath = module_path($this->moduleName, 'Resources/assets/js/admin/' . $this->modelJSName . '/index.js');
        $bootstrapJsPath = module_path($this->moduleName, 'Resources/assets/js/admin/index.js');
        $mainJsPath = resource_path('js/admin/index.js');

        if ($this->appendIfNotAlreadyAppended($indexJsPath, "import './Form';" . PHP_EOL)) {
            $this->info('Appending Form to ' . $indexJsPath . ' finished');
        };
        if ($this->appendIfNotAlreadyAppended($bootstrapJsPath, "import './" . $this->modelJSName . "';" . PHP_EOL)) {
            $this->info('Appending Form to ' . $bootstrapJsPath . ' finished');
        };
        if ($this->appendIfNotAlreadyAppended($mainJsPath, "import '@" . $this->moduleName . "';" . PHP_EOL)) {
            $this->info('Appending ' . $mainJsPath . '/index.js finished');
        };

        if ($this->strReplaceInFile(
            base_path('webpack.mix.js'),
            '|@' . $this->moduleName . '|',
            "// Do not delete me :) I'm used for auto-generation configuration",
            "'@" . $this->moduleName . "': __dirname + '/" . str_replace('\\', '/', $this->moduleNamespace) . '/Resources/assets/js/admin' . "'" . PHP_EOL . "           // Do not delete me :) I'm used for auto-generation configuration"
        )) {
            $this->info('Updating webpack configuration');
        }
    }

    protected function isUsedTwoColumnsLayout(): bool
    {
        return in_array("published_at", array_column($this->readColumnsFromTable($this->tableName)->toArray(), 'name'));
    }

    protected function buildForm()
    {

        return view('elifbyte/admin-module-generator::' . $this->form, [
            'moduleName' => Str::lower($this->moduleName),
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)->sortBy(function ($column) {
                return !($column['type'] == "json");
            }),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return ($column['type'] == "json") && ($this->translatable);
                })->count() > 0,
            'translatableTextarea' => ['perex', 'text', 'body'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildFormRight()
    {

        return view('elifbyte/admin-module-generator::' . $this->formRight, [
            'moduleName' => Str::lower($this->moduleName),
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelPlural' => $this->modelPlural,
            'modelDotNotation' => $this->modelDotNotation,
            'modelLangFormat' => $this->modelLangFormat,
            'modelVariableName' => $this->modelVariableName,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName)->sortBy(function ($column) {
                return !($column['type'] == "json");
            }),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return ($column['type'] == "json") && ($this->translatable);
                })->count() > 0,
            'translatableTextarea' => ['perex', 'text', 'body'],
            'relations' => $this->relations,
        ])->render();
    }

    protected function buildCreate()
    {

        return view('elifbyte/admin-module-generator::' . $this->create, [
            'moduleName' => Str::lower($this->moduleName),
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return ($column['type'] == "json") && ($this->translatable);
                })->count() > 0,
        ])->render();
    }


    protected function buildEdit()
    {

        return view('elifbyte/admin-module-generator::' . $this->edit, [
            'moduleName' => Str::lower($this->moduleName),
            'modelBaseName' => $this->modelBaseName,
            'modelRouteAndViewName' => $this->modelRouteAndViewName,
            'modelVariableName' => $this->modelVariableName,
            'modelPlural' => $this->modelPlural,
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelDotNotation' => $this->modelDotNotation,
            'modelJSName' => $this->modelJSName,
            'modelLangFormat' => $this->modelLangFormat,
            'resource' => $this->resource,
            'isUsedTwoColumnsLayout' => $this->isUsedTwoColumnsLayout(),

            'modelTitle' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                return in_array($column['name'], ['title', 'name', 'first_name', 'email']);
            })->first(null, ['name' => 'id'])['name'],
            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return ($column['type'] == "json") && ($this->translatable);
                })->count() > 0,
        ])->render();
    }

    protected function buildFormJs()
    {
        return view('elifbyte/admin-module-generator::' . $this->formJs, [
            'modelViewsDirectory' => $this->modelViewsDirectory,
            'modelJSName' => $this->modelJSName,
            'hasTranslatable' => $this->readColumnsFromTable($this->tableName)->filter(function ($column) {
                    return ($column['type'] == "json") && ($this->translatable);
                })->count() > 0,

            'columns' => $this->getVisibleColumns($this->tableName, $this->modelVariableName),
        ])->render();
    }

    protected function getOptions()
    {
        return [
            ['model-name', 'm', InputOption::VALUE_OPTIONAL, 'Generates a code for the given model'],
            ['belongs-to-many', 'btm', InputOption::VALUE_OPTIONAL, 'Specify belongs to many relations'],
            ['template', 't', InputOption::VALUE_OPTIONAL, 'Specify custom template'],
            ['force', 'f', InputOption::VALUE_NONE, 'Force will delete files before regenerating form'],
        ];
    }

}
