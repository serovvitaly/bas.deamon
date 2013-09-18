<?php

class HomeController extends BaseController {

    const ZIP_EXT = 'zip';
    
    public $layout = 'home.base';
    
    protected $_store_path = NULL;
    
    public function __construct()
    {
        $this->_store_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/store/';
        
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
        $files = UploadFile::orderBy('created_at', 'ASC')->get();
        
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
        
        $unique_name = md5( microtime() );
        
        $file = new UploadFile;
        $file->unique_name  = $unique_name;
        $file->load_start = time();
        $file->save();
        
        $upload_handler = new UploadHandler(array(
            'file_name' => $unique_name . '.' . self::ZIP_EXT,
            'complete_handler' => function($files)use(&$file){
                $f = $files;
                $file->size = $f->size;
                $file->load_stop = time();
            }
        ));
        
        $file->name = $upload_handler->original_file_names[0];
        $file->save();
        
        return '';
    }
    
    
    public function postUnpack()
    {
        $id = Input::get('id');
        
        $out = array(
            'success' => false,
            'result'  => NULL
        );
        
        if ($id> 0) {
            $file = UploadFile::where('status', '=', 0)->get();
            if ($this->_unpacker( $file[0]['id'] )) {
                $out['success'] = true;
            }
        }
        
        return json_encode($out);
    }
    
    
    public function getPo()
    {
        //echo phpinfo();return '';
        
        $this->_processing(1);
        
        return '';
    }
    
    
    /**
    * Выполняет извлечение файла со списком сайтов из загруженного архива
    * 
    * @param mixed $file_id
    */
    protected function _unpacker($file_id)
    {
        $file = UploadFile::find($file_id);
        
        $file_path = $this->_store_path . $file->unique_name . '.' . self::ZIP_EXT;
        
        if (file_exists($file_path)) {
            
            $zip = new ZipArchive;
            
            $unpacked_path = $this->_store_path . 'unpacked/' . $file->unique_name;
            
            if (!file_exists($unpacked_path)) {
                mkdir($unpacked_path, 0755);
            }
            
            if ($zip->open($file_path)) {
                if ( $zip->extractTo($unpacked_path) ) {
                    $file->status = 1;
                    $file->save();
                    
                    return true;
                }
                $zip->close();
            }
            
        }
        
        return false;
    }

    
    /**
    * Выполняет обработку извлеченного файла со списком сайтов
    * 
    * @param mixed $file_id
    */
    protected function _processing($ufile_id)
    {
        $ufile = UploadFile::find($ufile_id);
        
        $unpacked_dir_path = $this->_store_path . 'unpacked/' . $ufile->unique_name;
        
        if (file_exists($unpacked_dir_path)) {
            $collection = scandir($unpacked_dir_path);
            if (count($collection) > 0) {
                foreach ($collection AS $fitem) {
                    if( !is_dir($fitem) ) {
                        
                        $processing_file_path = rtrim($unpacked_dir_path, '/') . '/' . $fitem;
                        
                        if (file_exists($processing_file_path)) {
                            $this->_process_go($processing_file_path, $ufile_id);
                            break;
                        }
                        
                    }
                }
            }
        }
        
        return false;
    }
    
    
    /**
    * Выполняет обработку одного файла со списком сайтов
    * 
    * @param mixed $file_path
    */
    protected function _process_go($file_path, $ufile_id)
    {   
        system("php -f ../daemon/sposer.php {$file_path} {$ufile_id}");
    }
}                                                                                  
