<?php

class HomeController extends BaseController {

    public $layout = 'home.base';
    
    public function __construct()
    {
        $controller = $this;
        
        $this->afterFilter(function()use($controller){
            $controller->layout->action = 'all';
        });
    }
    
    public function getIndex()
    {
        $this->layout->content = View::make('home.index');
    }
    
    public function getLoad()
    {
        $this->layout->content = View::make('home.load');
    }
    
    public function getAll()
    {
        $sites = Site::take(40)->get();
        
        $this->layout->content = View::make('home.all', array('sites' => $sites));
    }
    
    public function getMeet()
    {
        $this->layout->content = View::make('home.meet');
    }
    
    public function getPages()
    {
        $this->layout->content = View::make('home.pages');
    }
    
    public function getConts()
    {
        $this->layout->content = View::make('home.conts');
    }
    
    public function getChecker()
    {
        $this->layout->content = View::make('home.checker');
    }
    
	public function getProven()
	{
		$this->layout->content = View::make('home.proven');
	}

}