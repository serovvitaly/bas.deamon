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
        
        return $sites = Site::where('status', '>=', $status)->paginate($take);
    }
    
    public function getIndex()
    {
        $domains = array(
            //'all'    => Site::all()->count(),
            'all'    => 0,
            //'meet'   => Site::where('status', 1)->count(),
            'meet'   => 0,
            'pages'  => 0,
            'conts'  => 0,
            'proven' => 0,
        );
        
        $this->layout->content = View::make('home.index', array('domains' => $domains));
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
        $uid = Input::get('uid');
        
        $url   = NULL;
        $pages = NULL;
        
        if ($uid > 0) {
            $dm = Site::find($uid);
            $url = $dm->url;
            
            $data = json_decode($dm->data);
            
            if (isset($data->result) AND is_array($data->result) AND count($data->result) > 0) {
                foreach ($data->result AS $page) {
                    if (isset($page->url)) {
                        $pages[] = array(
                            'url'       => $page->url,
                            'http_code' => $page->http_code,
                            'emails'    => $page->result->emails,
                            'phones'    => $page->result->phones,
                        );
                    }
                }
            }
        }
        
        $this->layout->content = View::make('home.checker', array('url' => $url, 'pages' => $pages));
    }
    
    
    public function postGetContent()
    {
        $url = Input::get('url');
        
        $url = strtolower($url);
            
        if (substr($url, 0, 8) == 'https://') {
            //
        } elseif (substr($url, 0, 7) == 'http://') {
            //
        } else {
            $url = 'http://' . $url;
        }
        
        return file_get_contents($url);
    }
    
    
    public function getProven()
    {
        $sites = $this->_sites(4);
        
        $this->layout->content = View::make('home.proven', array('sites' => $sites));
    }
    
    public function getDaemons()
    {
        ob_start();
        system("ps -ela");        
        $system = ob_get_contents();
        ob_end_clean();
        
        $mix = explode(PHP_EOL, trim($system, PHP_EOL));
        
        array_walk($mix, function(&$item, $key){
            $item = explode(' ', $item);
            array_walk($item, function(&$item, $key){
                if (empty($item)) return false;
            });
        });
        
        $remix = array();
        if (count($mix) > 1) {
            $head = $mix[0];
            unset($mix[0]);
            $body = $mix;
            foreach ($body AS $item) {
                if (count($item) > 0) {
                    $colex = array();
                    foreach ($item AS $key => $val) {
                        if (!empty($val) AND isset($head[$key])) {
                            $colex[ $head[$key] ] = $val;
                        }
                    }
                    $remix[] = $colex;
                }
            }
        }        
        $mix = $remix;
        
        
        $this->layout->content = View::make('home.daemons', array('mix' => $mix));
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
    
    
    public function postStartDaemon()
    {
        $root_path = dirname($_SERVER['DOCUMENT_ROOT']);
        $daemon_path = $root_path . '/daemon/daemon.php';
        $daemon_log_path = $root_path . '/daemon/logs/daemon.log';
        
        $command = "/usr/bin/php -f {$daemon_path} > {$daemon_log_path} &";
        
        exec($command);
        
        return json_encode(array(
            'success' => true
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
                $out['success'] = true;
            }
        }
        
        return json_encode($out);
    }
    
    
    public function postProcess()
    {
        $id = Input::get('id');
        
        $out = array(
            'success' => false,
            'result'  => NULL
        );
        
        if ($id > 0) {
            if ($this->_processing( $id )) {
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
        $ufile = UploadFile::find($file_id);
        
        if ($ufile->status != 1) {
            return false;
        }
        
        $file_path = $this->_store_path . $ufile->unique_name . '.' . self::ZIP_EXT;
        
        if (file_exists($file_path)) {
            
            $zip = new ZipArchive;
            
            $unpacked_path = $this->_store_path . 'unpacked/' . $ufile->unique_name;
            
            if (!file_exists($unpacked_path)) {
                mkdir($unpacked_path, 0755);
            }
            
            if ($zip->open($file_path)) {
                if ( $zip->extractTo($unpacked_path) ) {
                    $ufile->status = 2;
                    $ufile->save();
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
        
        if ($ufile->status != 2) {
            return false;
        }
        
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
                
                return true;
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
