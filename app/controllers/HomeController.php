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
        $this->layout->content = 'Главная';
    }
    
    public function getLoad()
    {
        $this->layout->content = 'Загрузка';
    }
    
    public function getAll()
    {
        $this->layout->content = 'Все';
    }
    
    public function getMeet()
    {
        $this->layout->content = 'Отвечают';
    }
    
    public function getPages()
    {
        $this->layout->content = 'Есть странцы';
    }
    
    public function getConts()
    {
        $this->layout->content = 'Есть контакты';
    }
    
    public function getChecker()
    {
        $this->layout->content = 'Проверка';
    }
    
	public function getProven()
	{
		$this->layout->content = 'Проверенные';
	}

}