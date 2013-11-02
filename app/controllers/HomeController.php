<?php

class HomeController extends BaseController {

    const ZIP_EXT = 'zip';
    
    public $layout = 'home.base';
    
    protected $_store_path = NULL;
    
    protected $_proven_status  = 4;
    protected $_proven_compare = '>=';
    
    public function __construct()
    {
        $this->_store_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/store/';
        
        $controller = $this;
        
        $this->afterFilter(function()use($controller){
            $controller->layout->action = 'all';
        });
    }
    
    protected function _sites($status = NULL, $condition = '>=')
    {
        $take = 50;
        
        if ($status === NULL) {
            return Site::orderBy('domain_created', 'DESC')->paginate($take);
        }
        
        return Site::where('status', $condition, $status)->orderBy('domain_created', 'DESC')->paginate($take);
    }
    
    public function getIndex()
    {         
        $this->layout->content = View::make('home.index');
    } 
    
    public function postGetCounts()
    {
        $res = DB::table('sites_list')
               ->select(DB::raw('status, COUNT(id) as count'))
               ->groupBy('status')
               ->get();
               
        $counts = array(
            0  => array('status' => 0, 'count' => 0),
            1  => array('status' => 1, 'count' => 0),
            2  => array('status' => 2, 'count' => 0),
            3  => array('status' => 3, 'count' => 0),
            4  => array('status' => 4, 'count' => 0),
        );
        
        if (is_array($res) AND count($res) > 0) {
            foreach ($res AS $row) {
                $counts[$row->status] = array(
                    'status' => $row->status,
                    'count'  => $row->count,
                );
            }
        }
        
        $counts[0]['count'] = $counts[0]['count'] + $counts[1]['count'] + $counts[2]['count'] + $counts[3]['count'] + $counts[4]['count'];
        $counts[1]['count'] = $counts[1]['count'] + $counts[2]['count'] + $counts[3]['count'] + $counts[4]['count'] . " ({$counts[1]['count']})";
        $counts[2]['count'] = $counts[2]['count'] + $counts[3]['count'] + $counts[4]['count'] . " ({$counts[2]['count']})";
        $counts[3]['count'] = $counts[3]['count'] + $counts[4]['count'] . " ({$counts[3]['count']})";
        
        return json_encode(array(
            'result' => $counts
        ));
    } 
    
    public function postCheckDaemon()
    {        
        $updated_at_min = date('Y-m-d H:i:s', time() - 3600);
        
        $res = DB::table('sites_list')
               ->select(DB::raw('status, COUNT(id) as count, updated_at'))
               ->where('updated_at', '>', $updated_at_min)
               ->groupBy('status')
               ->get();
                       
        $slist = array(
            -1 => 'не отвечает',
            0  => 'не обработан',
            1  => 'отвечает',
            2  => 'есть страницы',
            3  => 'есть контакты',
            4  => 'проверен',
        );
        
        $delta_mins = ceil( (time() - strtotime($updated_at_min)) / 60 );
        
        $status_content = "<p style='margin:10px 0 0'>Статистика обработки за последний час:</p>";
        
        $all_counts = 0;       
        if (is_array($res) AND count($res) > 0) {
            
            foreach ($res AS $row) {
                $status_content .= "{$slist[$row->status]} - {$row->count}<br>";
                $all_counts += $row->count;
            }
        }
        $status_content .= "ИТОГО - {$all_counts}";
        
        
        $urow = BaseSite::orderBy('updated_at', 'DESC')->take(1)->get();
        
        $updated_at = (string) $urow[0]->updated_at;
        
        $delta = time() - strtotime($updated_at);
        
        $mins = floor($delta / 60);
        
        $secs = $delta - $mins * 60;
        
        if ($mins >= 30) {
            $color = 'red';
        } elseif ($mins >= 10 AND $mins < 30) {
            $color = 'brown';
        } else {
            $color = 'green';
        }
        
        $content = "<span style='color:{$color}'>Демон обращался к базе {$mins} мин. {$secs}сек. назад</span>";
        
        return $content . $status_content;
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
    
    
    public function getGetUrlContent()
    {
        $url = Input::get('url');
        
        $options = array(
          'http'=>array(
            'method'=>"GET",
            'header'=>"Accept-language: en\r\n" .
                      //"Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                      "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
          )
        );

        $context = stream_context_create($options);
        
        $output = file_get_contents($url, false, $context);
       
        $url = rtrim($url, '/');
        $output = preg_replace('/(src|href)=\"\/?(?!(http:)|(https:))([a-zA-Z0-9а-яА-Я]+)/', '$1="'.$url.'/$4', $output);
        
        return $output;
    }
   
   
    public function postLoadUrlContent()
    {
        $url  = Input::get('url');
        $type = Input::get('type');
        
        $out['result'] = NULL;
        
        if (!empty($url)) {
            
            switch ($type) {
                case 'phone':
                    $ptn = Pattern::all();
                    $ptns = array();
                    if ($ptn AND count($ptn) > 0) {
                        foreach ($ptn AS $p) {
                            if (!empty($p->pattern)) {
                                
                                $temp_ptn = trim($p->pattern);
                                $temp_ptn = str_replace('*', '[0-9]', $temp_ptn);
                                $temp_ptn = str_replace('+', '\+', $temp_ptn);
                                $temp_ptn = str_replace(array('(',')'), array('[(]','[)]'), $temp_ptn);
                                
                                $ptns[] = $temp_ptn;
                            }
                        }
                    }
                    if (count($ptns) > 0) {
                        $pattern = '/(' . implode(')|(', $ptns) . ')/';
                    } else {
                        $pattern = NULL;
                    }
                    
                    //$pattern = '/((\\\\((8|7|\+7|\+\s7){0,1}(9){1}[0-9]{1}\\\\)[\s]{0,})|((8|7|\+7|\+\s7){0,1}[\s]{0,}[- \\\\(]{0,}([0-9]{3,4})[- \\\\)]{0,}))[0-9]{1,3}(-){0,}[0-9]{2}(-)[0-9]{2}/';
                    break;
                case 'email':
                    $pattern = '/([a-zA-Z0-9-_.]{1,})@([a-z0-9-]{1,}\.[a-z]{2,4}\.?[a-z]{0,4})/';
                    break;
                default :
                    $pattern = NULL;
            }
            
            //$out['pattern2'] = $pattern;
            
            if ($pattern !== NULL) {
                
                $options = array(
                  'http'=>array(
                    'method'=>"GET",
                    'header'=>"Accept-language: en\r\n" .
                              //"Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                              "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
                  )
                );

                $context = stream_context_create($options);
                $content = file_get_contents($url, false, $context);
                
                if ($content) {
                    
                    preg_match_all($pattern, $content, $matches1);
                    
                    //$out['url'] = $url;
                    //$out['content'] = $content;
                    
                    $content = strip_tags($content);
                    preg_match_all($pattern, $content, $matches2);
                    
                    //$out['data'] = array_merge_recursive($matches1, $matches2);
                    
                    $result = array();
                    
                    if (isset($matches1[0]) AND count($matches1[0]) > 0) {
                        foreach ($matches1[0] AS $mm) {
                            if (!empty($mm)) {
                                $result[] = $mm;
                            }
                        }
                    }
                    if (isset($matches2[0]) AND count($matches2[0]) > 0) {
                        foreach ($matches2[0] AS $mm) {
                            if (!empty($mm)) {
                                $result[] = $mm;
                            }
                        }
                    }
                    
                    $out['result'] = $result;
                }
            }
            
        }
        
        return json_encode($out);
    } 
    
    public function getChecker()
    {
        $_satus = 1;
        
        $uid = Input::get('uid');
        $url = Input::get('url');
        
        $filter_status = Input::get('filter_status', array(1,2,3));
        
        $dm_url = NULL;
        $pages  = NULL;
        
        $phones = array();
        $emails = array();
        $marker = '';
        
        $next_uid = NULL;
        
        $sites = Site::where('status', '>=', $_satus)->where('status', '<', 4)->whereIn('status', $filter_status)->orderBy('domain_created', 'DESC')->paginate(50);
        
        foreach ($filter_status AS $filter_stat) {
            $sites->appends('filter_status['.$filter_stat.']', $filter_stat);
        }
        
        if ($uid > 0 AND ($dm = Site::find($uid))) {
            $dm_url = $dm->url;
            $marker = $dm->marker;
            
            $phones = array_unique( explode(',', $dm->phones) );
            $emails = array_unique( explode(',', $dm->emails) );
            
            $data = json_decode($dm->data);            
            $nexts = Site::where('id', '>', $uid)->where('status', '>', $_satus)->where('status', '<', 4)->whereIn('status', $filter_status)->orderBy('domain_created', 'DESC')->take(1)->get(array('id'));
            if (count($nexts) > 0) {
                $next_uid = $nexts[0]['id'];
            }
        }
        
        if (!$url) $url = $dm_url;
        
        $this->layout = View::make('layout-fluid');
        
        $this->layout->content = View::make('home.checker', array(
            'uid'      => $uid,
            'url'      => $url,
            'marker'   => $marker,
            'next_uid' => $next_uid,
            'sites'    => $sites,
            'phones'   => $phones,
            'emails'   => $emails,
            'filter_status'   => $filter_status,
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
                
                $site->status = 4;
                $site->marker = $marker;
                
                $site->save();
                
                $this->_getSitesTree(true);
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
                        
                        $days = array_reverse($days);
                        
                        $months[] = array(
                            'title'    => (isset($months_list[$m_index]) ? $months_list[$m_index] : $tree_month) . " ({$month_count})",
                            'isFolder' => false,
                            'isLazy'   => false,
                            'children' => $days
                        );
                        
                        $year_count += $month_count;
                    }
                    
                    $months = array_reverse($months);
                    
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
    
    
    protected function _getSitesTree($overwrite = false)
    {
        $_cache_key = 'sites_tree';
        
        if (Cache::has($_cache_key) AND $overwrite == false) {
            //return Cache::get($_cache_key);
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
        
        $sites = Site::where('status', $this->_proven_compare, $this->_proven_status)->where('updated_at', '>=', $date.' 00:00:00')->where('updated_at', '<=', $date.' 23:59:59')->get(array('url','meet_links','delegated','phones','emails','updated_at','marker'));
        
        $out = "URL;LINKS;DELEGATED;PHONE-1;PHONE-2;PHONE-3;EMAIL-1;EMAIL-2;EMAIL-3;DATE;MARKER\n";
        
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
                    iconv('UTF-8', 'WINDOWS-1251', $site->marker),
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
        $this->layout->content = View::make('home.daemons', array(
            'patterns' => Pattern::orderBy('pattern', 'DESC')->get()
        ));
    }
    
    
    public function postSavePattern()
    {
        $id = Input::get('pid');
        $pattern = Input::get('pattern');
        
        $out = array(
            'success' => false,
            'result'  => NULL,
        );
        
        if (!empty($pattern)) {
            
            $pt = ($id > 0) ? Pattern::find($id) : new Pattern;
            $pt->pattern = $pattern;
            $pt->save();
            
            $out = array(
                'success' => true
            );
        }
        
        return json_encode($out);
    }
    
    
    public function postCheckReg()
    {
        $list   = Input::get('list');
        $reg_id = Input::get('reg_id');
        
        $out['result'] = NULL;
        
        if (!empty($list) AND $reg_id > 0) {
            $pattern = Pattern::find($reg_id);
            
            if ($pattern AND !empty($pattern->pattern)) {
                
                $ptn = $pattern->pattern;
                $ptn = str_replace('*', '[0-9]', $ptn);
                $ptn = str_replace('+', '\+', $ptn);
                $ptn = str_replace(array('(',')'), array('[(]','[)]'), $ptn);
                $ptn = "/{$ptn}/";
                
                @preg_match_all($ptn, $list, $matches);
                //echo $ptn . "\n";
                //print_r($matches);
                
                if ($matches AND isset($matches[0]) AND count($matches[0]) > 0) {
                    $results = array();
                    
                    foreach ($matches[0] AS $matche) {
                        $matche = trim($matche);
                        if (!empty($matche)) {
                            $results[] = $matche;
                        }
                    }
                    
                    $out['result'] = $results;
                }
                
            }
        }
        
        return json_encode($out);
    }
    
    
    public function getRemovePattern()
    {
        $pid = Input::get('pid');
        
        if ($pid > 0) {
            Pattern::find($pid)->delete();
        }
        
        return Redirect::to('/daemons');
    }
    
    
    public function getEditPattern()
    {
        $pid = Input::get('pid');
        
        if ($pid > 0) {
            //
        }
        
        return Redirect::to('/daemons');
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
        $token = '3246346257yterrtt3466rehjg45hdhkjg46557uvgmgtt4t56ygh67u46';
        
        file_get_contents("http://zwrk006.fvds.ru/control.php?token={$token}&action=run");
        
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
