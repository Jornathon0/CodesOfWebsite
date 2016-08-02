<?php

/**
 * 
 * @author     Shadingyu <dingwengeng@juwang.com>
 * @copyright  Copyright (c) Juwang Technologies Inc.
 * @version    $Id$
 */

namespace ZHT\Controller;

use Think\Controller;

class PagesController extends CommonController {
    public function index() {
        $this->assign($array);
        $this->display();
    }
}
