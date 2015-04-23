<?php
/**
 * Created by PhpStorm.
 * User: Lanny Lee
 * Date: 2014/12/27
 * Time: 13:04
 */

namespace Home\Controller;

use Think\Controller;

class ResourceController extends Controller {

    public function jsBase() {
        C('LAYOUT_ON', FALSE);
        C('SHOW_PAGE_TRACE', FALSE);
        $this->theme('default')->display('jsBase', '', 'text/javascript');
    }

}