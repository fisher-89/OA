<?php

namespace App\Http\Controllers\Api\HR;

use Encypt;
use Validator;
use Carbon\Carbon;
use App\Models\HR;
use Illuminate\Http\Request;
use App\Services\StaffService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessRequest;
use App\Http\Resources\HR\StaffResource;
use App\Http\Resources\HR\StaffCollection;
use App\Http\Requests\StoreStaffRequest as StaffRequest;

class StaffController extends Controller
{
    protected $staffService;

    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 不允许返回全部用户
        if (! $request->has('page')) $request->merge(['page' => 1]);

        preg_match('/role\.id=(.*?)(;|$)/', $request->filters, $match);
        $roleId = false;
        if ($match) {
            $roleId = is_numeric($match[1]) ? $match[1] : json_decode($match[1], true);
            $newFilters = preg_replace('/role\.id=.*?(;|$)/', '$3', $request->filters);
            $request->offsetSet('filters', $newFilters);
        }
        $list = HR\Staff::withApi()
            ->when($roleId, function ($query) use ($roleId) {
                $query->whereHas('role', function ($query) use ($roleId) {
                    if (is_array($roleId)) {
                        $query->whereIn('id', $roleId);
                    } else {
                        $query->where('id', $roleId);
                    }
                });
            })
            ->filterByQueryString()
            ->sortByQueryString()
            ->withPagination();

        return array_merge($list, [
            'data' => new StaffCollection($list['data'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StaffRequest $request)
    {
        $data = $request->all();
        $curd = $this->staffService->create($data);
        if ($curd['status'] == 1) {
            $staff = HR\Staff::withApi()
                ->orderBy('staff_sn', 'desc')
                ->first();

            return response()->json(StaffResource::make($staff), 201);
        }

        return response()->json($curd, 422);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(StaffRequest $request)
    {
        $data = $request->all();
        $curd = $this->staffService->update($data);
        if ($curd['status'] == 1 || $curd['status'] == -1) {
            $staff = HR\Staff::withApi()->where('staff_sn', $data['staff_sn'])->first();

            return response()->json(StaffResource::make($staff), 201);
        }

        return response()->json([
            'errors' => true,
            'message' => '服务器错误！',
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function show(HR\Staff $staff)
    {
        $staff->load(['relative', 'position', 'department', 'brand', 'shop', 'cost_brands', 'tags', 'tags.category']);
        $staff->oa = app('Authority')->getAuthoritiesByStaffSn($staff->staff_sn);

        return StaffResource::make($staff);
    }

    /**
     * 获取员工关系人数据.
     * 
     * @param  HR\Staff $staff
     * @return array
     */
    public function relations(HR\Staff $staff)
    {
        if ($staff->relative->isEmpty()) {
            return response()->json(['nodes' => [], 'edges' => []]);
        }
        $nodes = [[
            'staff_sn' => $staff->staff_sn,
            'image' => 'http://120.79.121.158:8101/default_avatar.png',
            'name' => $staff->realname,
            'current' => 1,
        ]];
        $links = [];
        $this->makeRelation($staff->relative, $staff->staff_sn, $nodes, $links);

        return response()->json(['nodes' => $nodes, 'edges' => $links]);
    }

    public function makeRelation($relative, $current, &$nodes, &$links)
    {
        $relative->map(function ($item) use ($current, &$nodes, &$links) {
            $has = collect($nodes)->filter(function ($node) use ($item) {
                return $node['staff_sn'] === $item->staff_sn;
            });
            if ($has->isEmpty()) {
                $nodes[] = [
                    'staff_sn' => $item->staff_sn,
                    'name' => $item->realname,
                    'image' => 'http://120.79.121.158:8101/default_avatar.png',
                ];
            }
            $source = collect($nodes)->filter(function ($node) use ($item) {
                return $node['staff_sn'] === $item->pivot->staff_sn;
            });
            $target = collect($nodes)->filter(function ($node) use ($item) {
                return $node['staff_sn'] === $item->pivot->relative_sn;
            });
            array_push($links, [
                "source" => $source->keys()[0],
                "target" => $target->keys()[0],
                "relation" => $this->relativeType()[$item->pivot->relative_type],
            ]);
            if ($item->staff_sn === $current) {
                return false;
            }
            if ($item->relative->isNotEmpty()) {
                $this->makeRelation($item->relative, $current, $nodes, $links);
            }
        });
    }

    /**
     * 获取关系类型.
     * 
     * @return StaffRelativeType
     */
    public function relativeType()
    {
        return  \Cache::remember('CacheRelativeType', now()->addMonth(), function () {
            return HR\StaffRelativeType::get()->mapWithKeys(function ($item) {
                return [$item->id => $item->name];
            });
        });
    }

    /**
     * 员工变动日志列表。
     *
     * @param  Staff $staff
     * @return mixed
     */
    public function logs(HR\Staff $staff)
    {
        $logs = HR\StaffLog::with('staff', 'admin')
            ->where('staff_sn', $staff->staff_sn)
            ->whereNotIn('operation_type', ['active', 'delete'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($logs, 200);
    }

    public function formatLog(HR\Staff $staff)
    {
        $logs = HR\StaffLog::where('is_show', 1)
            ->where('staff_sn', $staff->staff_sn)
            ->orderBy('id', 'asc')
            ->get();
        $format = $logs->map(function ($item) use ($staff) {
            $changes = [];
            $transfer = ['人事变动', '导入变动', '职位变动'];
            if (in_array($item->operation_type, $transfer)) {
                $allow = [
                    'department', 'position', 'brand', 'status', 'cost_brands', 
                    '部门全称', '职位', '品牌', '员工状态', '费用品牌'
                ];
                $changes = collect($item->changes)->only($allow)->values();
            }
            $item->transfer = $changes;
            return $item;
        });
        return response()->json($format);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function destroy(HR\Staff $staff)
    {
        $staff->delete();

        return response()->json(null, 204);
    }

    /**
     * 重置密码 (默认：123456)
     *
     * @param \App\Models\HR\Staff $staff
     * @return mixed
     */
    public function resetPass(HR\Staff $staff)
    {
        $salt = mt_rand(100000, 999999);
        $newPass = Encypt::password('123456', $salt);

        $staff->password = $newPass;
        $staff->salt = $salt;
        $staff->save();

        return response()->json(['message' => '重置成功'], 201);
    }

    /**
     * 解锁员工.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return mixed
     */
    public function unlock(HR\Staff $staff)
    {
        abort_if($staff->is_active !== 0, 422, '员工未锁定，无法激活！');
        $staff->is_active = 1;
        $staff->save();

        return response()->json([
            'message' => '激活成功',
            'changes' => ['is_active' => 1],
        ], 201);
    }

    /**
     * 锁定员工.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return mixed
     */
    public function locked(HR\Staff $staff)
    {
        abort_if($staff->is_active !== 1, 422, '员工未激活，无法锁定！');
        $staff->is_active = 0;
        $staff->save();

        return response()->json([
            'message' => '锁定成功',
            'changes' => ['is_active' => 0],
        ], 201);
    }

    /**
     * 转正操作.
     *
     * @param  Request $request
     * @return mixed
     */
    public function process(ProcessRequest $request)
    {
        $data = $request->all();
        $this->staffService->update($data);
        $operateAt = Carbon::parse($data['operate_at'])->gt(now());

        return response()->json([
            'message' => $operateAt ? '预约成功' : '操作成功',
            'changes' => $operateAt ? [] : $data,
        ], 201);
    }

    /**
     * 人事变动操作。
     *
     * @param  Request $request
     * @return mixed
     */
    public function transfer(ProcessRequest $request)
    {
        $data = $request->all();
        $this->staffService->update($data);
        $data['cost_brands'] = HR\CostBrand::whereIn('id', $data['cost_brands'])->get();
        $operateAt = Carbon::parse($data['operate_at'])->gt(now());

        return response()->json([
            'message' => $operateAt ? '预约成功' : '操作成功',
            'changes' => $operateAt ? [] : $data,
        ], 201);
    }

    /**
     * 离职操作。
     *
     * @param  Request $request
     * @return mixed
     */
    public function leave(ProcessRequest $request)
    {
        $data = $request->all();
        $this->staffService->update($data);
        $operateAt = Carbon::parse($data['operate_at'])->gt(now());
        if (!$data['skip_leaving'] && $data['status_id'] != -2) {
            $data['status_id'] = 0;
        }

        return response()->json([
            'message' => $operateAt ? '预约成功' : '操作成功',
            'changes' => $operateAt ? [] : $data,
        ], 201);
    }

    /**
     * 处理离职交接.
     *
     * @param  Request $request
     * @return mixed
     */
    public function leaving(ProcessRequest $request)
    {
        $this->staffService->update($request->all());
        $operateAt = Carbon::parse($request->operate_at)->gt(now());
        if (!$operateAt) {
            $staff = HR\Staff::find($request->staff_sn);
            $request->merge(['status_id' => $staff->status_id]);
        }

        return response()->json([
            'message' => $operateAt ? '预约成功' : '操作成功',
            'changes' => $operateAt ? [] : $request->all(),
        ], 201);
    }

}
