<?php 
namespace Controller;

class Meet extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/meet.blade');
    }
}
