<?php 
namespace Controller;

class Load extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/load.blade');
    }
}

