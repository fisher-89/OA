<?php 

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\HR\Staff as StaffModel;
use App\Services\Tools\OperationLogs\StaffOperationLogService;

class StaffService
{
    /**
     * 变更数据
     * 
     * @var array
     */
    protected $dirty = [];

    /**
     * 变更日志服务.
     * 
     * @var StaffOperationLogService
     */
    protected $logService;

    /**
     * 可预约操作类型
     * 
     * @var array
     */
    protected $types = ['transfer', 'import_transfer', 'employ', 'leave', 'leaving', 'reinstate'];

    public function __construct(StaffOperationLogService $logService)
    {
        $this->logService = $logService;
    }

    public function create($data)
    {
        $this->save(array_except($data, ['staff_sn']));

        return [
            'status' => 1,
            'message' => '添加成功',
        ];
    }

    public function update($data)
    {
        $this->save($data);

        if ($this->isDirty()) {
            return [
                'status' => 1,
                'message' => '编辑成功',
            ];
        } else {
            return [
                'status' => -1, 
                'message' => '未发现改动'
            ];
        }
    }

    public function save(array $data)
    {
        if (isset($data['staff_sn']) && !empty($data['staff_sn'])) {
            $model = StaffModel::find($data['staff_sn']);
        } else {
            $model = new StaffModel();
        }

        $this->fillDataAndSave($model, $data);
    }

    /**
     * 保存一条数据
     * 
     * @param type $model
     * @param type $data
     * @throws \Illuminate\Database\QueryException
     */
    protected function fillDataAndSave($model, $data)
    {
        $this->reset();

        \DB::beginTransaction();
        try {
            $model->fill($data);
            $this->saving($model, $data);
            if (! $this->hasTransfer($data)) {
                $this->addDirty($model);
                $model->save();
                // $this->saved($model, $data);
                $this->changeBelongsToMany($model, $data);
                if ($this->isDirty()) {
                    $this->logService->model($model)->write($this->dirty, $data);
                }
            }
            \DB::commit();

        } catch (\Exception $err) {

            Log::error($err->getMessage());
            \DB::rollBack();
            throw $err;
        }
    }

    /**
     * 是否可预约操作。
     * 
     * @param  [type]  $data
     * @return boolean
     */
    protected function hasTransfer($data)
    {
        if (
            in_array($data['operation_type'], $this->types) && 
            strtotime($data['operate_at']) > strtotime(date('Y-m-d'))
        ) {
            return true;
        }
        return false;
    }

    /**
     * 多对多关联同步
     * @param $model
     */
    protected function changeBelongsToMany($model, $data)
    {
        if (array_has($data, 'relatives')) {
            $relatives = collect($data['relatives']) ? : collect([]);
            $relationQuery = $model->relative();
            $original = $relationQuery->get();

            $input = [];
            $relatives->map(function ($item) use (&$input) {
                $input[$item['relative_sn']] = $item;
            });

            $dirty = $relationQuery->sync($input);
            $changed = $relationQuery->get();
            if (!empty(array_filter($dirty))) {
                $this->dirty['relative'] = $this->makeBelongsToManyDirty($dirty, $original, $changed);
            }

        }
        if (array_has($data, 'cost_brands')) {
            $cost_brands = $data['cost_brands'] ? : [];
            $relationQuery = $model->cost_brands();
            $original = $relationQuery->get();

            $dirty = $relationQuery->sync($cost_brands);
            $changed = $relationQuery->get();
            if (!empty(array_filter($dirty))) {
                $this->dirty['cost_brands'] = $this->makeBelongsToManyDirty($dirty, $original, $changed);
            }
        }
        if (array_has($data, 'tags')) {
            $tags = $data['tags'] ? : [];
            $relationQuery = $model->tags();
            $original = $relationQuery->get();

            $dirty = $relationQuery->sync($tags);
            $changed = $relationQuery->get();
            if (!empty(array_filter($dirty))) {
                $this->dirty['tags'] = $this->makeBelongsToManyDirty($dirty, $original, $changed);
            }
        }
    }

    /**
     * 生成多对多Dirty数据
     * @param type $response 改变的关系id
     * @param type $original 改变前的关系数据
     * @param type $changed 改变后的关系数据
     * @return type
     */
    protected function makeBelongsToManyDirty($response, $original, $changed)
    {   
        $newAttached = [];
        foreach ($response['attached'] as $v) {
            $pivot = $changed->find($v)->pivot;
            $order = $this->getPivotAttribute($pivot);
            $newAttached[$v] = $order;
        }
        $response['attached'] = $newAttached;
        $newDetached = [];
        foreach ($response['detached'] as $v) {
            $pivot = $original->find($v)->pivot;
            $order = $this->getPivotAttribute($pivot);
            $newDetached[$v] = $order;
        }
        $response['detached'] = $newDetached;
        $newUpdated = [];
        foreach ($response['updated'] as $v) {
            $current = $original->find($v)->pivot;
            $order = $changed->find($v)->pivot->toArray();
            $newUpdated[$v] = $this->getDirtyWithOriginal($current->fill($order));
        }
        $response['updated'] = $newUpdated;

        return $response;
    }

    /**
     * 获取中间表的额外字段
     * @param type $pivot
     * @return type
     */
    protected function getPivotAttribute($pivot)
    {
        if (count($pivot->toArray()) === 2) {
            return array_except($pivot->toArray(), [$pivot->getForeignKey()]);
        }
        return array_except($pivot->toArray(), [$pivot->getForeignKey(), $pivot->getOtherKey()]);
    }

    public function saving($model, array $data)
    {
        $this->operating($model, $data);

        $operationType = $data['operation_type'];
        if (
            ($operationType === 'leave') && 
            ($model->status_id !== -2)
        ) {
            $this->setLeaving($model);
        } elseif (
            in_array($operationType, $this->types) && 
            strtotime($data['operate_at']) > strtotime(date('Y-m-d'))
        ) {
            $this->transferLater($model, $data);

        } elseif (
            $operationType === 'leaving' && 
            strtotime($data['operate_at']) <= strtotime(date('Y-m-d'))
        ) {
            $leaving = $model->leaving;
            $model->setAttribute('status_id', $leaving->original_status_id);

            $leaving->delete();
        }
    }

    protected function saved($model, $data)
    {
        // 如果是离职操作并且跳过了离职交接 修改操作类型
        if (
            array_has($data, 'skip_leaving') && 
            $data['operation_type'] === 'leave' && 
            $data['skip_leaving']
        ) {
            $data['operation_type'] = 'leaving';
            $this->save($data);
        }
    }

    /**
     * 设置离职记录。
     * 
     * @param [type] $model
     */
    private function setLeaving($model)
    {
        $model->leaving()->create([
            'staff_sn' => $model->staff_sn,
            'original_status_id' => $model->status_id,
        ]);
        $model->setAttribute('status_id', 0);
    }

    /**
     * 创建一条预约操作(将执行操作延后数据不做任何处理).
     * 
     * @param  Staff $model
     * @param  array $data
     */
    private function transferLater($model, $data)
    {
        $islock = $model->tmp()->where('status', 1)->count();
        $model->tmp()->create([
            'changes' => $data,
            'admin_sn' => $data['admin_sn'] ?? app('CurrentUser')->getStaffSn(),
            'operate_at' => $data['operate_at'],
            'status' => $islock ? 0 : 1,
        ]);
    }

    /**
     * 加入Dirty
     * @param type $model
     * @param type $relation
     */
    protected function addDirty($model, $relation = null)
    {
        $dirty = $this->getDirtyWithOriginal($model);

        if (! empty($relation)) {
            $dirty = [$relation => $dirty];
        }

        $this->dirty = array_collapse([$this->dirty, $dirty]);
    }

    /**
     * 重置 Dirty，LogService
     */
    public function reset()
    {
        $this->dirty = [];
    }

    /**
     * 检查模型及其关联是否有Dirty（变动）
     * @return type
     */
    protected function isDirty()
    {
        return !empty($this->dirty);
    }

    /**
     * 获取带有原值的Dirty
     * @param type $model
     * @return type
     */
    protected function getDirtyWithOriginal($model)
    {
        $dirty = [];
        foreach ($model->getDirty() as $key => $value) {
            $dirty[$key] = [
                'original' => $model->getOriginal($key, ''),
                'dirty' => $value,
            ];
        }
        return $dirty;
    }

    private function operating($model, array $data)
    {
        $operateAt = $data['operate_at'];
        $operationType = $data['operation_type'];

        switch ($operationType) {
            case 'entry':
                $model->setAttribute('hired_at', $operateAt);
                break;
            case 'import_entry':
                // $model->setAttribute('hired_at', $operateAt);
                break;
            case 'reinstate':
                $model->setAttribute('hired_at', $operateAt);
                $model->setAttribute('employed_at', null);
                $model->setAttribute('left_at', null);
                $model->setAttribute('is_active', 1);
                break;
            case 'leave':
                $model->setAttribute('left_at', $operateAt);
                break;
            case 'leaving':
                // $model->setAttribute('is_active', 0);
                break;
        }
        if (empty($model->employed_at) && $model->status_id > 1) {
            $model->setAttribute('employed_at', $operateAt);
        }
    }

}   