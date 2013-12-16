<?php 
namespace Controller;

class Conts extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/conts.blade');
    }
}
