<?php 

namespace App\Http\Controllers\Api\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffTmp;
use App\Models\HR\CostBrand;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffTmpController extends Controller
{
	
    /**
     * get all records for staff。
     * 
     * @param  Staff  $staff
     * @return mixed
     */
    public function index(Staff $staff)
    {
        $list = $staff->tmp()->with(['staff', 'admin'])->get();
        $list->map(function ($item) {
            $item->changes = $this->trans($item);

            return $item;
        });

        return response()->json($list, 200);
    }

    /**
     * get a single record.
     * 
     * @param  Staff  $staff
     * @return mixed
     */
    public function show(StaffTmp $tmp)
    {
        $tmp->load(['staff', 'admin']);

        return response()->json($tmp, 200);
    }

    /**
     * restore a sigle record.
     * 
     * @param  StaffTmp $tmp
     * @return mixed
     */
    public function restore(StaffTmp $tmp)
    {
        abort_if($tmp->status !== 1, 422, '禁止还原');

        $tmp->status = 2;
        $tmp->getConnection()->transaction(function () use ($tmp) {
            $tmp->save();

            // 解锁下一条被锁定的记录.
            if ($nextTmp = StaffTmp::byLock()->oldest('operate_at')->first()) {
            	$nextTmp->status = 1;
            	$nextTmp->save();
            }
        });
        $tmp->load(['staff', 'admin']);

        return response()->json($tmp, 201);
    }

    /**
     * 翻译预约变动字段.
     * 
     * @param  object $item
     * @return array
     */
    public function trans($item): array
    {
        $trans = trans('fields.staff');
        $relationMap = [
            'shop_sn' => 'shop.name',
            'brand_id' => 'brand.name',
            'status_id' => 'status.name',
            'position_id' => 'position.name',
            'department_id' => 'department.name',
        ];
        $changes = [];
        foreach ($item->changes as $key => $change) {
            if (array_has($relationMap, $key)) {
                $relationKey = $relationMap[$key];
                $key = $trans[$relationKey];
                $value = $this->getRelationName($relationKey, $change);
            }
            if (is_array($change)) {
                $key = $trans[$key];
                $value = CostBrand::whereIn('id', $change)->pluck('name');
            }
            $changes[$key] = $value;
        }

        return $changes;
    }

    /**
     * 获取员工变动关系名称.
     * 
     * @param  string $relationKey
     * @param  int $change
     * 
     * @return string
     */
    public function getRelationName($relationKey, $change)
    {
        $model = new Staff;
        $relation = explode('.', $relationKey)[0];
        $result = $model->$relation()->getModel()->find($change);

        return $result->getAttribute('name');
    }
}