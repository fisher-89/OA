<?php

namespace App\Models;

use DB;
use App\Models\HR\Staff;
use App\Models\Traits\ListScopes;
use App\Services\AuthorityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes, ListScopes;

    protected $guarded = ['id', 'manager_name'];

    protected $fillable = [
        'name',
        'cate_id',
        'brand_id',
        'manager_sn',
        'manager_name',
        'parent_id',
        'source_id',
        'is_locked',
        'province_id',
        'minister_sn',
        'minister_name',
        'area_manager_sn',
        'area_manager_name',
        'personnel_manager_sn',
        'personnel_manager_name',
        'regional_manager_sn',
        'regional_manager_name',
        'sort',
    ];


    public static function boot()
    {
        parent::boot();

        self::saving(function ($post) {
            $post->changeFullName();
        });
        self::saved(function ($post) {
            $post->changeRoleAuthority();
        });
    }

    /* ----- 定义关联Start ----- */

    public function _parent()
    { //上级部门
        return $this->belongsTo('App\Models\Department', 'parent_id');
    }

    public function children()
    {
        return $this->_children()->with('children');
    }

    public function _children()
    { //下级部门
        return $this->hasMany('App\Models\Department', 'parent_id')->orderBy('sort', 'asc');
    }

    public function manager()
    { //部门主管
        return $this->belongsTo('App\Models\HR\Staff', 'manager_sn', 'staff_sn');
    }

    public function position()
    { //关联职位
        return $this->belongsToMany('App\Models\Position', 'department_has_positions');
    }

    public function authority()
    { //权限
        return $this->belongsToMany('App\Models\Authority', 'department_has_authorities')->orderBy('parent_id', 'asc');
    }

    public function staff()
    { //员工
        return $this->hasMany('App\Models\HR\Staff')->orderBy('staff_sn', 'asc');
    }

    public function role()
    { //所属角色
        return $this->belongsToMany('App\Models\Role', 'role_has_departments')->orderBy('id', 'asc');
    }

    public function brand()
    { //品牌
        return $this->belongsTo('App\Models\Brand')->withTrashed();
    }

    public function category()
    {
        return $this->hasOne(DepartmentCategory::class, 'id', 'cate_id');
    }

    /* ----- 定义关联End ----- */


    /* ----- 访问器Start ----- */

    public function getParentIdAttribute($value)
    {
        return intval($value);
    }

    public function getParentsAttribute()
    {
        $parent = $this->_parent;
        if (empty($parent)) {
            $parents = [];
        } else {
            $parents = $parent->parents;
            array_push($parents, [
                'id' => $parent->id,
                'name' => $parent->name,
                'full_name' => $parent->full_name,
            ]);
        }

        return $parents;
    }

    public function getParentIdsAttribute()
    {
        $parent = $this->_parent;
        if (empty($parent)) {
            $parentIds = [];
        } else {
            $parentIds = $parent->parentIds;
            array_unshift($parentIds, $parent->id);
        }
        return $parentIds;
    }

    public function getAllChildrenAttribute()
    {
        $children = $this->_children;
        if (!empty($children)) {
            $children = array_map(function ($value) {
                $value['all_children'] = $value->all_children;
                return $value;
            }, $children);
        }
        return $children;
    }

    public function getChildrenIdsAttribute()
    {
        if (isset($this->_children) && $this->_children) {
            $childrenIds = $this->_children->pluck('id');
            foreach ($this->_children as $child) {
                $childrenIds = array_collapse([$childrenIds, $child->childrenIds]);
            }
            return $childrenIds;
        }
    }

    public function getTopAttribute()
    { //获取顶级部门
        if ($this->parent_id == 0) {
            return $this;
        } else {
            return $this->_parent->top;
        }
    }

    public function getOptionAttribute($level = 0)
    { //获取option
        $data = '<option value="' . $this->id . '">';
        $data .= $this->full_name . '</option>';
        foreach ($this->_children as $v) {
            $data .= $v->getOptionAttribute($level + 1);
        }
        return $data;
    }

    public function getExcelTdAttribute($level = 0)
    {
        $data = '<tr><td>' . $this->full_name . '</td><td>' . $this->id . '</td></tr>';
        foreach ($this->_children as $v) {
            $data .= $v->getExcelTdAttribute($level + 1);
        }
        return $data;
    }

    /* ----- 访问器End ----- */

    /* ----- 修改器Start ----- */

    public function setParentIdAttribute($value)
    {
        $this->attributes['parent_id'] = $value ?: 0;
    }

    /* ----- 修改器End ----- */

    /* ----- 本地作用域 Start ----- */

    public function scopeVisible($query)
    {
        $authorityService = new AuthorityService;
        $departments = $authorityService->getAvailableDepartments();
        $query->whereIn('id', $departments);
    }

    public function scopeApi($query)
    {
        $query->with('brand', '_parent', '_children');
    }

    /* ----- 本地作用域 End ----- */
    public static function deleteByTrees($departmentId)
    {
        $self = self::find($departmentId);
        if ($self->is_locked) {
            return ['status' => -1, 'message' => '包含已锁定部门,请联系技术人员'];
        } elseif ($self->staff->where('status_id', '>=', 0)->count() > 0) {
            return ['status' => -1, 'message' => '当前部门有在职员工，无法删除'];
        } else {
            foreach ($self->_children as $child) {
                $result = self::deleteByTrees($child->id);
                if ($result['status'] == -1) {
                    return $result;
                }
            }
            $self->delete();
            return ['status' => 1, 'message' => '删除成功'];
        }
    }

    public static function reOrder($departments, $parentId = 0)
    {
        foreach ($departments as $k => $v) {
            $department = self::find($v['id']);
            $department->parent_id = $parentId;
            $department->sort = $k + 1;
            $department->save();
            if (isset($v['children'])) {
                self::reOrder($v['children'], $v['id']);
            }
        }
    }

    /**
     * 更新部门全称
     */
    private function changeFullName()
    {
        if ($this->isDirty('parent_id') || $this->isDirty('name')) {
            $newFullName = $this->parent_id > 0 ? $this->_parent->full_name . '-' . $this->name : $this->name;
            $this->full_name = $newFullName;
            $this->changeChildrenFullName($newFullName);
        } elseif ($this->isDirty('full_name')) {
            $newFullName = $this->full_name;
            $this->changeChildrenFullName($newFullName);
        }
    }

    private function changeChildrenFullName($fullName)
    {   
        if(isset($this->_children) && $this->_children)
        {
            $this->_children->each(function ($item) use ($fullName) {
                $item->full_name = $fullName . '-' . $item->name;
                $item->save();
            });
        }
    }

    /**
     * 根据父级继承部门权限
     */
    private function changeRoleAuthority()
    {
        if ($this->isDirty('parent_id')) {
            $originalParentId = $this->getOriginal('parent_id');
            $rolesOrigin = DB::table('role_has_departments')->where('department_id', $originalParentId)->pluck('role_id')->toArray();
            $rolesNew = DB::table('role_has_departments')->where('department_id', $this->parent_id)->pluck('role_id')->toArray();
            $curRoles = DB::table('role_has_departments')->where('department_id', $this->id)->pluck('role_id')->toArray();
            if ($rolesOrigin != $rolesNew) {
                $this->role()->detach(array_diff($rolesOrigin, $rolesNew));
                $this->role()->attach(array_diff($rolesNew, $rolesOrigin, $curRoles));
                $this->changeChildrenRoleAuthority($rolesOrigin, $rolesNew, $curRoles);
            }
        }
    }

    private function changeChildrenRoleAuthority($rolesOrigin, $rolesNew, $curRoles)
    {
        if(isset($this->_children) && $this->_children)
        {
            $this->_children->each(function ($item) use ($rolesOrigin, $rolesNew, $curRoles) {
                $item->role()->detach(array_diff($rolesOrigin, $rolesNew));
                $item->role()->attach(array_diff($rolesNew, $rolesOrigin, $curRoles));
                $item->changeChildrenRoleAuthority($rolesOrigin, $rolesNew, $curRoles);
            });
        }
    }

}
