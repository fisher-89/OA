<?php

namespace App\Http\Controllers\HR;

use App\Contracts\CURD;
use App\Models\HR\Attendance\WorkingSchedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkingScheduleController extends Controller
{

    protected $transPath = 'fields.working_schedule';
    protected $curdService;

    public function __construct(CURD $curdContract)
    {
        $this->curdService = $curdContract;
    }

    public function showManagePage()
    {
        return view('hr.attendance.working_schedule');
    }

    public function getList(Request $request)
    {
        $date = $request->has('working_schedule_date') ? $request->working_schedule_date : date('Y-m-d');
        $model = new WorkingSchedule(['ymd' => date('Ymd', strtotime($date))]);
        return app('Plugin')->dataTables($request, $model->visible());
    }

    public function getInfo(Request $request)
    {
        $id = $request->id;
        $date = $request->date;
        $model = new WorkingSchedule(['ymd' => date('Ymd', strtotime($date))]);
        $staffSn = preg_replace('/^(.*?)\-.*$/', '$1', $id);
        $shopSn = preg_replace('/^.*?\-(.*)$/', '$1', $id);
        $response = $model->where('staff_sn', $staffSn)->where('shop_sn', $shopSn)->first()->toArray();
        $response['id'] = $id;
        $response['date'] = $date;
        return $response;
    }

    public function addOrEdit(Request $request)
    {
        $this->validate($request, $this->makeValidator($request), [], trans($this->transPath));
        if (
            !empty($request->clock_in) &&
            !empty($request->clock_out) &&
            strtotime($request->clock_in) > strtotime($request->clock_out)
        ) {
            return ['status' => -1, 'message' => '下班时间不能早于上班时间'];
        }
        $date = $request->date;
        if (empty($request->clock_in)) $request->offsetSet('clock_in', null);
        if (empty($request->clock_out)) $request->offsetSet('clock_out', null);
        if ($request->has('id')) {
            $id = $request->id;
            $staffSn = preg_replace('/^(.*?)\-.*$/', '$1', $id);
            $shopSn = preg_replace('/^.*?\-(.*)$/', '$1', $id);
        } else {
            $staffSn = $request->staff_sn;
            $shopSn = $request->shop_sn;
        }
        $model = new WorkingSchedule(['ymd' => $date]);
        $request->offsetUnset('date');
        $workingSchedule = $model->where('staff_sn', $staffSn)->where('shop_sn', $shopSn)->first();
        if ($request->shop_duty_id == 1) {
            $model->where('shop_sn', $shopSn)->where('shop_duty_id', 1)->update(['shop_duty_id' => 3]);
        }
        if ($request->has('id')) {
            $request->offsetUnset('id');
            $model->where('staff_sn', $staffSn)->where('shop_sn', $shopSn)->update($request->only(['clock_in', 'clock_out', 'shop_duty_id']));
            return ['status' => 1, 'message' => '编辑成功'];
        } else if (!empty($workingSchedule)) {
            return ['status' => -1, 'message' => '已有相同的排班存在'];
        } else {
            $model->fill($request->except(['_url']))->setDate($date)->save();
            return ['status' => 1, 'message' => '添加成功'];
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $date = $request->date;
        $model = new WorkingSchedule(['ymd' => date('Ymd', strtotime($date))]);
        $staffSn = preg_replace('/^(.*?)\-.*$/', '$1', $id);
        $shopSn = preg_replace('/^.*?\-(.*)$/', '$1', $id);
        $model->where('staff_sn', $staffSn)->where('shop_sn', $shopSn)->delete();
        return ['status' => 1, 'message' => '删除成功'];
    }

    protected function makeValidator($input)
    {
        $validator = [
            'shop_duty_id' => ['required', 'exists:attendance.shop_duty,id'],
            'date' => ['required'],
            'clock_in' => ['regex:/^\d{2}(:\d{2}){1,2}$/'],
            'clock_out' => ['regex:/^\d{2}(:\d{2}){1,2}$/'],
        ];
        if (empty($input['id'])) {
            $validator['shop_sn'] = ['required', 'exists:shops,shop_sn,deleted_at,NULL'];
            $validator['staff_sn'] = ['required_with:staff_name'];
            $validator['staff_name'] = ['required', 'exists:staff,realname,staff_sn,' . $input['staff_sn']];
        }
        return $validator;
    }
}
