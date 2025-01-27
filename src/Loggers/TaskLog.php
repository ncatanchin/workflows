<?php

namespace The42Coders\Workflows\Loggers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TaskLog extends Model
{
    protected $table = 'task_logs';

    public static $STATUS_START = 'start';
    public static $STATUS_FINISHED = 'finished';
    public static $STATUS_ERROR = 'error';

    protected $fillable = [
        'status',
        'workflow_log_id',
        'task_id',
        'name',
        'message',
        'start',
        'end',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public static function createHelper(int $workflow_log_id, int $task_id, string $task_name, string $status = null, string $message = '', $start = null, $end = null): TaskLog
    {
        return TaskLog::create([
            'status' => $status ?? self::$STATUS_START,
            'workflow_log_id' => $workflow_log_id,
            'task_id' => $task_id,
            'name' => $task_name,
            'message' => $message,
            'start' => $start ?? Carbon::now(),
            'end' => $end,
        ]);
    }

    public function setError(string $errorMessage)
    {
        $this->message = $errorMessage;
        $this->status = self::$STATUS_ERROR;
        $this->end = Carbon::now();
        $this->save();
    }

    public function finish()
    {
        $this->status = self::$STATUS_FINISHED;
        $this->end = Carbon::now();
        $this->save();
    }
}
