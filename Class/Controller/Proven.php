<?php 
namespace Controller;

class Proven extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/proven.blade');
    }
}