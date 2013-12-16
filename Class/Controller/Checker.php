<?php 
namespace Controller;

class Checker extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/checker.blade');
    }
}
