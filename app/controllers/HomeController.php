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
        $files = UploadFile::orderBy('created_at', 'DESC')->get();
        
        $this->layout->content = View::make('home.load', array('files' => $files));
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
    
    public function postUpload()
    {
        error_reporting(E_ALL | E_STRICT);
        require_once('../workbench/vs/fileupload/src/VS/FileUpload/UploadHandler.php');
        
        $file_name = md5( microtime() ) . '.zip';
        
        $file = new UploadFile;
        $file->file_name  = $file_name;
        $file->load_start = time();
        $file->save();
        
        $upload_handler = new UploadHandler(array(
            'file_name' => $file_name,
            'complete_handler' => function($files)use(&$file){
                $f = $files[0];
                $file->size = $f->size;
                $file->load_stop = time();
            }
        ));
        
        $file->name = $upload_handler->original_file_names[0];
        $file->save();
        
        return '';
    }

}