<?php 
namespace Controller;

class Meet extends \MyController
{
    public function run()
    {
        $this->sites = $this->_sites(1);
        
        $this->content = new \Micro\View('home/meet.blade');
    }
}
