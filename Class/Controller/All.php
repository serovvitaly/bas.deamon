<?php
namespace Controller;

class All extends \MyController
{
    public function run()
    {
        $this->sites = $this->_sites();
        
        $this->content = new \Micro\View('home/all.blade');
    }
}
