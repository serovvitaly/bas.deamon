<?php 
namespace Controller;

class Conts extends \MyController
{
    public function run()
    {
        $this->sites = $this->_sites(3);
        
        $this->content = new \Micro\View('home/conts.blade');
    }
}
