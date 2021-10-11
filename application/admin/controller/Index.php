<?php

namespace app\admin\controller;

use think\Db;

class Index extends Base
{
    public function index()
    {

        $this->assign('roles_id', $this->roles);
        return $this->fetch();
    }

    public function home()
    {
      
        return $this->fetch();
    }
    public function welcome()
    {
      
        return $this->fetch();
    }
}