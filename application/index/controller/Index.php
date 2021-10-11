<?php

namespace app\index\controller;

use think\Db;

class Index extends Base
{
    public function index()
    {
        echo "2112222";
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