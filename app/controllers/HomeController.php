<?php

class HomeController extends BaseController {

    const ZIP_EXT = 'zip';
    
    public $layout = 'home.base';
    
    protected $_store_path = NULL;
    
    protected $_proven_status  = 1;
    protected $_proven_compare = '>';
    
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
        $take = 50;
        
        if ($status === NULL) {
            return Site::paginate($take);
        }
        
        return Site::where('status', '>=', $status)->paginate($take);
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
        $_satus = 1;
        
        $uid = Input::get('uid');
        $url = Input::get('url');
        
        $dm_url = NULL;
        $pages  = NULL;
        
        $phones = array();
        $emails = array();
        $marker = '';
        
        $next_uid = NULL;
        
        $sites = $this->_sites($_satus);
        
        if ($uid > 0 AND ($dm = Site::find($uid))) {
            $dm_url = $dm->url;
            $marker = $dm->marker;
            
            $phones = array_unique( explode(',', $dm->phones) );
            $emails = array_unique( explode(',', $dm->emails) );
            
            $data = json_decode($dm->data);            
            $nexts = Site::where('id', '>', $uid)->where('status', '>', $_satus)->take(1)->get(array('id'));
            if (count($nexts) > 0) {
                $next_uid = $nexts[0]['id'];
            }
        }
        
        if (!$url) $url = $dm_url;
        
        
        $this->layout->content = View::make('home.checker', array(
            'uid'      => $uid,
            'url'      => $url,
            'marker'   => $marker,
            'next_uid' => $next_uid,
            'sites'    => $sites,
            'phones'   => $phones,
            'emails'   => $emails,
        ));
    }
    
    
    public function postSaveData()
    {
        $uid      = Input::get('uid');
        $next_uid = Input::get('next_uid');
        $marker   = Input::get('marker');
        $phones   = Input::get('phones');
        $emails   = Input::get('emails');
        
        if ($uid > 0) {
            $site = Site::find($uid);
            if ($site) {
                if ($phones) {
                    $phones = implode(',', array_unique( $phones ));
                    $site->phones = $phones;
                    $site->phones_count = count($phones);
                }
                if ($emails) {
                    $emails = implode(',', array_unique( $emails ));
                    $site->emails = $emails;
                    $site->emails_count = count($emails);
                }
                
                $site->marker = $marker;
                
                $site->save();
            }
        }
        
        return Redirect::to("/checker?uid={$next_uid}");
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
    
    
    public function postAjaxTree()
    {
        $root = Input::get('root');
        $root = str_replace(' ', '', $root);
        
        $parent_id = Input::get('parent_id');
        $parent_id = str_replace(' ', '', $parent_id);
        
        $output = array();
        
        $months_list = array(
            1  => 'Январь',
            2  => 'Февраль',
            3  => 'Март',
            4  => 'Апрель',
            5  => 'Май',
            6  => 'Июнь',
            7  => 'Июль',
            8  => 'Август',
            9  => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        );
        
        
        if (!empty($root)) {
            $root = explode('-', $root);
            $root_key = isset($root[0]) ? $root[0] : NULL;
            $root_val = isset($root[1]) ? $root[1] : NULL;
            
            $output = array();
            if (!empty($root_key) AND !empty($root_val)) {
                switch ($root_key) {                        
                        
                    case 'day':
                    
                        $date = explode('.', $root_val);
                        $date = "{$date[2]}-{$date[1]}-{$date[0]}";
                        
                        $_take = 50;
                    
                        $sites = Site::where('status', $this->_proven_compare, $this->_proven_status)->where('updated_at', '>=', $date.' 00:00:00')->where('updated_at', '<=', $date.' 23:59:59');
                        
                        $items = array();
                        $total = $sites->count();
                        $pages = ceil($total / $_take);
                        $current_page = Input::get('page');
                        $_GET['page'] = $current_page;
                        
                        $sites = $sites->paginate($_take);
                        
                        if (count($sites) > 0) {
                            foreach ($sites AS $item) {
                                
                                $phones = explode(',', strtolower($item->phones));
                                $phones = array_unique($phones);
                                if (is_array($phones) AND count($phones) > 0) {
                                    $phones = array_splice($phones, 0, 3);
                                } else {
                                    $phones = array();
                                }
                                
                                $emails = explode(',', strtolower($item->emails));
                                $emails = array_unique($emails);
                                if (is_array($emails) AND count($emails) > 0) {
                                    $emails = array_splice($emails, 0, 3);
                                } else {
                                    $emails = array();
                                }
                                
                                $items[] = array(
                                    'uid'        => $item->id,
                                    'url'        => $item->url,
                                    'marker'     => $item->marker,
                                    'meet_links' => $item->meet_links,
                                    'delegated'  => $item->delegated,
                                    'status'     => $item->status,
                                    'emails'     => implode('<br>', $emails),
                                    'phones'     => implode('<br>', $phones),
                                    'updated_at' => $item->updated_at,
                                );
                            }
                        }
                        
                        $output = array(
                            'items' => $items,
                            'take'  => $_take,
                            'total' => $total,
                            'pages' => $pages,
                            'current_page' => $current_page,
                            'paginate' => (string) $sites->links()
                        );
                        
                        break;
                        
                    default:
                        //
                }
            }
        } else {
            $tree = $this->_getSitesTree();
            if (count($tree) > 0) {
                foreach ($tree AS $tree_year => $tree_months) {
                    $months = array();
                    $year_count = 0;
                    foreach ($tree_months AS $tree_month => $tree_days) {
                        $m_index = intval($tree_month);
                        if ($tree_month < 10) $tree_month = '0' . $tree_month; 
                        $days = array();
                        $month_count = 0;
                        foreach ($tree_days AS $day => $count) {
                            if ($day < 10) $day = '0' . $day; 
                            $days[] = array(
                                'title'    => "{$day} ({$count})",
                                'isFolder' => false,
                                'isLazy'   => false,
                                'id'       => "day-{$day}.{$tree_month}.{$tree_year}"
                            );
                            
                            $month_count += $count;
                        }
                        
                        $months[] = array(
                            'title'    => (isset($months_list[$m_index]) ? $months_list[$m_index] : $tree_month) . " ({$month_count})",
                            'isFolder' => false,
                            'isLazy'   => false,
                            'children' => $days
                        );
                        
                        $year_count += $month_count;
                    }
                    
                    $output[] = array(
                        'title' => $tree_year . " ({$year_count})",
                        'isFolder' => true,
                        'isLazy'   => false,
                        'children' => $months
                    );
                }
            }
        }
        
        return json_encode($output);
    }
    
    
    protected function _getSitesTree()
    {
        $_cache_key = 'sites_tree';
        
        if (Cache::has($_cache_key)) {
            return Cache::get($_cache_key);
        }
        
        $sites = Site::where('status', $this->_proven_compare, $this->_proven_status)->groupBy('updated_at')->get(array('updated_at'));
        $mix = array();
        if (count($sites) > 0) {
            foreach ($sites AS $site) {
                $year  = intval(date('Y', strtotime($site->updated_at)));
                
                if ($year > 2012) {
                    $month = intval(date('m', strtotime($site->updated_at)));
                    $day   = intval(date('d', strtotime($site->updated_at)));
                    
                    if (isset($mix[$year]) AND isset($mix[$year][$month]) AND isset($mix[$year][$month][$day])) {
                        $mix[$year][$month][$day]++;
                    } else {
                        $mix[$year][$month][$day] = 1;
                    }    
                }
                
            }
        }
        
        Cache::add($_cache_key, $mix, 60 * 6);
        
        return $mix;
    }
    
    
    public function getExport()
    {        
        $date = Input::get('date');
        $date = explode('.', $date);
        $date = "{$date[2]}-{$date[1]}-{$date[0]}";
        
        $sites = Site::where('status', $this->_proven_compare, $this->_proven_status)->where('updated_at', '>=', $date.' 00:00:00')->where('updated_at', '<=', $date.' 23:59:59')->get(array('url','meet_links','delegated','phones','emails','updated_at'));
        
        $out = "URL;LINKS;DELEGATED;PHONE-1;PHONE-2;PHONE-3;EMAIL-1;EMAIL-2;EMAIL-3;DATE\n";
        
        if (count($sites) > 0) {
            foreach ($sites AS $site) {
                
                $phones = array_unique( explode(',', strtolower($site->phones)) );
                $emails = array_unique( explode(',', strtolower($site->emails)) );
                
                $out .= implode(';', array(
                    $site->url,
                    $site->meet_links,
                    $site->delegated,
                    isset($phones[0]) ? $phones[0] : '',
                    isset($phones[1]) ? $phones[1] : '',
                    isset($phones[2]) ? $phones[2] : '',
                    isset($emails[0]) ? $emails[0] : '',
                    isset($emails[1]) ? $emails[1] : '',
                    isset($emails[2]) ? $emails[2] : '',
                    $site->updated_at,
                )) . "\n";
            }
        }
        
        $headers = array(
            'Content-Type' => 'application/csv',
            'Content-Disposition' => 'attachment; filename="sites_export_'.$date.'.csv"',
        );
        
        return Response::make($out, 200, $headers);
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
