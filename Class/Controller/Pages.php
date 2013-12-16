<?php 
namespace Controller;

class Pages extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/pages.blade');
    }
}
