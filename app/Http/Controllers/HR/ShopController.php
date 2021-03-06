<?php

namespace App\Http\Controllers\HR;

use App\Http\Requests;
use App\Contracts\CURD;
use App\Models\HR\Shop;
use Illuminate\Http\Request;
use App\Contracts\OperationLog;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{

    protected $model = 'App\Models\HR\Shop';

    public function __construct(CURD $curd, OperationLog $logService)
    {
        $this->logService = $logService->table('shop_log');
        $this->curdService = $curd->log($this->logService)->model($this->model);
    }

    public function showManagePage()
    {
        return view('hr.shop');
    }

    public function getList(Request $request)
    {
        return app('Plugin')->dataTables($request, Shop::visible());
    }

    public function getInfo(Request $request)
    {
        $id = $request->id;
        $shop = Shop::with(['staff'])->find($id);
        return $shop;
    }

    public function addOrEdit(Request $request)
    {
        $this->validate($request, $this->makeValidator($request), [], trans('fields.shop'));
        return $this->curdService->createOrUpdate($request->all());
    }

    /**
     * 删除
     * @param Request $request
     * @return array
     */
    public function deleteByOne(Request $request)
    {
        $response = $this->curdService->delete($request->id, ['staff']);
        return $response;
    }

    /**
     * 搜索店铺插件
     * @param Request $request
     * @return type
     */
    public function showSearchResult(Request $request)
    {
        $data['name'] = $request->name;
        $data['target'] = json_encode($request->target);
        $data['mark'] = $request->has('mark') ? '_' . $request->mark : '';
        return view('hr/search_shop')->with($data);
    }

    protected function makeValidator(Request $request)
    {
        $validator = [
            'shop_sn' => ['required', 'max:10'],
            'name' => ['required', 'max:50'],
            'department_id' => ['required', 'integer', 'min:2', 'exists:departments,id,deleted_at,NULL'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'province_id' => ['required', 'exists:i_district,id,level,1'],
            'city_id' => ['required', 'exists:i_district,id,level,2'],
            'county_id' => ['required', 'exists:i_district,id,level,3'],
            'address' => ['required', 'max:50', 'regex:/^[^\'"]*$/'],
            'clock_in' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'clock_out' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'manager_sn' => ['required_with:manager_name'],
            'manager_name' => [],
        ];
        if (!empty($request->id)) {
            $validator['shop_sn'][] = 'exists:shops,shop_sn,id,' . $request->id;
        } else {
            $validator['shop_sn'][] = 'unique:shops,shop_sn,NULL,id,deleted_at,NULL';
        }
        return $validator;
    }

    /**
     * 保存店铺位置信息.
     *
     * @param Request $request
     * @return void
     */
    public function position(Request $request)
    {
        $shop = Shop::find($request->id);
        if ($shop === null) {
            return '店铺数据错误';
        }
        $amap = createRequest('/api/amap', 'post', [
            'shop_sn' => $shop->shop_sn,
            'shop_name' => $shop->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);
        $shop->lng = $request->longitude;
        $shop->lat = $request->latitude;
        $shop->geo_hash = $request->geo_hash;
        $shop->save();
        
        return response()->json(['message' => '操作成功', 'status' => 1], 201);
    }
}
