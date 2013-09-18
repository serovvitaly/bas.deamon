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
    
    protected function _sites($status = NULL)
    {
        $take = 40;
        
        if ($status === NULL) {
            return Site::paginate($take);
        }
        
        return $sites = Site::where('status', $status)->paginate($take);
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
        $sites = $this->_sites();
        
        $this->layout->content = View::make('home.all', array('sites' => $sites));
    }
    
    public function getMeet()
    {
        $sites = $this->_sites(1);
        
        $this->layout->content = View::make('home.meet', array('sites' => $sites));
    }
    
    public function getPages()
    {
        $sites = $this->_sites(2);
        
        $this->layout->content = View::make('home.pages', array('sites' => $sites));
    }
    
    public function getConts()
    {
        $sites = $this->_sites(3);
        
        $this->layout->content = View::make('home.conts', array('sites' => $sites));
    }
    
    public function getChecker()
    {
        $this->layout->content = View::make('home.checker');
    }
    
    public function getProven()
    {
        $this->layout->content = View::make('home.proven');
    }
    
    public function postSmartupdater()
    {
        $ids = Input::get('ids');
        
        $files = UploadFile::whereIn('id', $ids)->get();
        
        $result = array();
        if ($files) {
            foreach ($files AS $file) {
                $result[] = array(
                    'id'                => $file->id,
                    'status'            => $file->status,
                    'number_lines'      => $file->number_lines,
                    'number_lines_proc' => $file->number_lines_proc,
                );
            }
        }
        
        return json_encode(array(
            'success' => true,
            'result'  => $result
        ));
    }
    
    public function postUpload()
    {
        $file_id = Input::get('id');
        
        if ($file_id < 1) {
            return '';
        }
        
        $file = UploadFile::find($file_id);
        
        if (!$file) {
            return '';
        }
        
        $unique_name = md5( microtime() );
        
        $file->unique_name  = $unique_name;
        $file->load_start = time();
        $file->save();
        
        error_reporting(E_ALL | E_STRICT);
        require_once('../workbench/vs/fileupload/src/VS/FileUpload/UploadHandler.php');
        
        $upload_handler = new UploadHandler(array(
            'file_name' => $unique_name . '.' . self::ZIP_EXT,
            'complete_handler' => function($files)use(&$file){
                $f = $files;
                $file->size      = $f->size;
                $file->status    = 1;
                $file->load_stop = time();
            }
        ));
        
        $file->name = $upload_handler->original_file_names[0];
        $file->save();
        
        return '';
    }
    
    
    public function postFadd()
    {
        $file = new UploadFile;
        $file->save();
        
        return json_encode(array(
            'success' => true,
            'id' => $file->id
        )); 
    }
    
    
    public function postUnpack()
    {
        $id = Input::get('id');
        
        $out = array(
            'success' => false,
            'result'  => NULL
        );
        
        if ($id > 0) {
            if ($this->_unpacker( $id )) {
                $this->_processing( $id );
                $out['success'] = true;
            }
        }
        
        return json_encode($out);
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
                    $file->status = 2;
                    $file->save();
                    $zip->close();
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
        $root_path = dirname($_SERVER['DOCUMENT_ROOT']);
        $daemon_path = $root_path . '/daemon/sposer.php';
        $daemon_log_path = $root_path . '/daemon/logs/sposer.log';
        
        $command = "/usr/bin/php -f {$daemon_path} {$root_path} {$file_path} {$ufile_id} > {$daemon_log_path} &";
        
        exec($command);
    }
}                                                                                  
