<?php

namespace The42Coders\Workflows\Triggers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use The42Coders\Workflows\DataBuses\DataBus;
use The42Coders\Workflows\DataBuses\DataBussable;
use The42Coders\Workflows\Fields\Fieldable;
use The42Coders\Workflows\Jobs\ProcessWorkflow;
use The42Coders\Workflows\Loggers\WorkflowLog;

class Trigger extends Model
{
    use DataBussable, Fieldable;

    protected $table = 'triggers';

    public $family = 'trigger';

    public static $icon = '<i class="fas fa-question"></i>';

    protected $fillable = [
        'workflow_id',
        'parent_id',
        'type',
        'name',
        'data',
        'node_id',
        'pos_x',
        'pos_y',
    ];

    public static $output = [];
    public static $fields = [];
    public static $fields_definitions = [];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public static $commonFields = [
        'Description' => 'description',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function children()
    {
        return $this->morphMany('The42Coders\Workflows\Tasks\Task', 'parentable');
    }

    /**
     * Return Collection of models by type.
     *
     * @param array $attributes
     * @param null  $connection
     *
     * @return \App\Models\Action
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $entryClassName = '\\'.Arr::get((array) $attributes, 'type');

        if (class_exists($entryClassName)
            && is_subclass_of($entryClassName, self::class)
        ) {
            $model = new $entryClassName();
        } else {
            $model = $this->newInstance();
        }

        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    public function start(Model $model, array $data = [])
    {
        $log = WorkflowLog::createHelper($this->workflow, $model, $this);
        $dataBus = new DataBus($data);
        ProcessWorkflow::dispatch($model, $dataBus, $this, $log);
    }

    public function getSettings()
    {
        return view('workflows::layouts.settings_overlay', [
            'element' => $this,
        ]);
    }
}
