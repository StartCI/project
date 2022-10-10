<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CodeIgniter;

/**
 * Description of jquery
 *
 * @author felipe
 */
class Jquery {

    var $selector = '';
    var $js = null;

    public function __construct($selector = '') {
        $this->selector = $selector;
        $this->js = js();
    }

    public function __call($name, $arguments) {
        foreach ($arguments as $key => $value) {
            $arguments[$key] = $this->js->encode($value);
        }
        $a = implode(',', $arguments);
        if ($this->selector)
            echo "$('{$this->selector}').{$name}({$a});";
        else
            echo "$.{$name}({$a});";
    }

}
