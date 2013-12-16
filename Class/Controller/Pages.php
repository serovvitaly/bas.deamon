<?php 
namespace Controller;

class Pages extends \MyController
{
    public function run()
    {
        $this->sites = $this->_sites(2);
        
        $this->content = new \Micro\View('home/pages.blade');
    }
}
