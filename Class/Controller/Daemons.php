<?php 
namespace Controller;

class Daemons extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/daemons.blade');
    }
}
