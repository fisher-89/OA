<?php

/**
 * 当前用户功能类
 * create by Fisher 2017/1/15 <fisher9389@sina.com>
 */

namespace App\Services;

use App\Models\HR\Staff;

class CurrentUserService {

    public function __get($name) {
        return isset(session('admin')[$name]) ? session('admin')[$name] : '无效属性';
    }

    /**
     * 获取员工编号
     * @return int
     */
    public function getStaffSn() {
        return session('admin')['staff_sn'];
    }

    /**
     * 获取姓名
     * @return string
     */
    public function getName() {
        return session('admin')['realname'];
    }

    /**
     * 获取当前员工信息
     * @return array
     */
    public function getInfo() {
        return array_except(session('admin'), ['user_token', 'user_token_expiration']);
    }

    /**
     * 检查员工是否登录
     * @return type
     */
    public function isLogin() {
        return session()->has('admin');
    }

    /**
     * 判断当前员工是否为开发者
     * @return boolean
     */
    public function isDeveloper() {
        return session('admin')['username'] == 'developer' ? true : false;
    }

}
