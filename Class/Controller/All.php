<?php
namespace Controller;

class All extends \MyController
{
    public function run()
    {
        $this->content = new \Micro\View('home/all.blade');
    }
}
