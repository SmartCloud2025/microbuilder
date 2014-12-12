<?php
/**
 * 会话处理
 */
namespace App\Behavior;
use Core\Model\Acl;

class AuthBehavior {
    public function run(&$params) {
        session('__:uid', 1);
    }
}