<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Resources\CurrentUserResource;
use App\Http\Resources\HR\StaffCollection;
use App\Http\Resources\HR\StaffResource;
use App\Models\HR\Staff;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        preg_match('/role\.id=(.*?)(;|$)/', $request->filters, $match);
        $roleId = false;
        if ($match) {
            $roleId = is_numeric($match[1]) ? $match[1] : json_decode($match[1], true);
            $newFilters = preg_replace('/role\.id=.*?(;|$)/', '$3', $request->filters);
            $request->offsetSet('filters', $newFilters);
        }
        $list = Staff::when($roleId, function ($query) use ($roleId) {
            $query->whereHas('role', function ($query) use ($roleId) {
                if (is_array($roleId)) {
                    $query->whereIn('id', $roleId);
                } else {
                    $query->where('id', $roleId);
                }
            });
        })->filterByQueryString()
            ->sortByQueryString()
            ->withPagination();
        if (isset($list['data'])) {
            $list['data'] = new StaffCollection(collect($list['data']));
            return $list;
        } else {
            return new StaffCollection($list);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function show(Staff $staff)
    {
        return new StaffResource($staff);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\HR\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Staff $staff)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HR\Staff $staff
     * @return \Illuminate\Http\Response
     */
    public function destroy(Staff $staff)
    {
        //
    }


    public function getCurrentUser()
    {
        $staffSn = app('CurrentUser')->staff_sn;
        if ($staffSn == 999999) {
            $currentUser = config('auth.developer');
            $currentUser['authorities'] = [
                'oa' => app('Authority')->getAuthoritiesByStaffSn($staffSn),
                'available_brands' => app('Authority')->getAvailableBrandsByStaffSn($staffSn),
                'available_departments' => app('Authority')->getAvailableDepartmentsByStaffSn($staffSn),
                'available_shops' => app('Authority')->getAvailableShopsByStaffSn($staffSn),
            ];
            return $currentUser;
        } else {
            $currentUser = Staff::find($staffSn);
            return new CurrentUserResource($currentUser);
        }
    }
}
