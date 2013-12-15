<?php

$db = new mysqli('localhost', 'root', 'Sy9YGKbG', 'test');

$cont = 'all';
$cont = strtolower($cont);

if (!in_array($cont, array('all','meet','pages','conts'))) {
    die('Page not found!');
}

$page = (isset($_GET['page']) AND $_GET['page'] > 0) ? $_GET['page'] : 1;

$limit = 50;
$start = ($page - 1) * $limit;

$items = $db->query("SELECT * FROM `final_sites_list` WHERE `status` >= 2 ORDER BY `domain_created` DESC LIMIT {$start},{$limit}");

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" type="text/css" href="/packages/bootstrap/css/docs.css">
    <link rel="stylesheet" type="text/css" href="/packages/bootstrap/css/bootstrap.min.css">
    
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="/packages/jQuery-File-Upload-8.8.5/js/vendor/jquery.ui.widget.js"></script>
    <script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.iframe-transport.js"></script>
    <script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.fileupload.js"></script>
    <script src="/packages/jquery.periodic.js"></script>
    
    <style>
      .uloader{
          height: 18px;
          border: 1px solid #AEC7A5;
          background: #EFF5ED;
      }
      .uloader .ufiller{
          height: 18px;
          background: #BDE6B0;
      }
      .uloader .ucounter {
          margin-top: -19px;
          text-align: center;
      }
      .uloader.blue{
          border: 1px solid #6B94D3;
          background: #EAEEF5;
      }
      .uloader.blue .ufiller{
          background: #B3C9F0;
      }
      #informer{
          padding-left: 10px;
      }
      #informer .loader{
          background: url(/packages/dynatree/src/skin/loading.gif) no-repeat center left;
          padding: 5px 10px;
          display: none;
      }
      .cls-small{
          font-size: 10px;
      }
      ul.dynatree-container {
          border: none !important;
      }
      .check-table > tbody > tr > td{
          overflow: hidden;
      }
      .popoverdzen{
          position: absolute;
          margin: -28px 0 0 209px;
          border: 1px solid #AAA;
          background: #FFF;
          -webkit-box-shadow: 3px 4px 3px rgba(0, 0, 0, 0.23);
             -moz-box-shadow: 3px 4px 3px rgba(0, 0, 0, 0.23);
                  box-shadow: 3px 4px 3px rgba(0, 0, 0, 0.23);
      }
    </style>
    
    <script>
    
    function post(url, data, success){
        
        if (!data) data = {};
        if (!success) success = function(){};
        
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: success
            
        });
    }
     
    </script>
    
</head>
<body>

  <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="/">Загрузко 2.0</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li><a href="/"><i class="icon-home icon-white" title="Главная"></i></a></li>
              <li><a href="/load">Загрузка</a></li>
              <li<? ($cont == 'all'   ? ' class="active"' : '') ?>><a href="/all">Все</a></li>
              <li<? ($cont == 'meet'  ? ' class="active"' : '') ?>><a href="/meet">Отвечают</a></li>
              <li<? ($cont == 'pages' ? ' class="active"' : '') ?>><a href="/pages">Есть странцы</a></li>
              <li<? ($cont == 'conts' ? ' class="active"' : '') ?>><a href="/conts">Есть контакты</a></li>
              <li><a href="/checker">Проверка</a></li>
              <li><a href="/proven">Проверенные</a></li>
              <li><a href="/daemons">Управление</a></li>
            </ul>
          </div>
        </div>
      </div>
  </div>

  <div class="container" style="padding-top: 20px;">

<table id="main-grid" class="table table-condensed table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>Домен</th>
      <th>Страницы</th>
      <th>Делигирован</th>
      <th style="width: 86px;">Статус</th>
      <th>Телефоны</th>
      <th>Email-ы</th>
      <th>Дата п. обр</th>
      <th>Возраст домена, дн.</th>
    </tr>
  </thead>
  <tbody>
  <?
  
  $delegated = array(
      'DELEGATED' => 'ДА',
      'NOT DELEGATED' => 'НЕТ',
  );
  
  $statuses = array(
      0 => 'не обработан',
      1 => 'отвечает',
      2 => 'есть страницы',
      3 => 'есть контакты',
      4 => 'проверен',
  );
  
  if ($items->num_rows > 0) {
      while ($item = $items->fetch_assoc()) {
          ?>
    <tr>
      <td><a href="/checker?uid=<?= $item['id'] ?>"><?= $item['url'] ?></a></td>
      <td><?= $item['meet_links'] ?></td>
      <td><?= $delegated[$item['delegated']] ?></td>
      <td><?= $statuses[$item['status']] ?></td>
      <td style="text-align: center;"><?= ($item['phones_count'] > 0) ? '<div class="popover" data-toggle="popover" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus." data-placement="right" src="/packages/icons/tick_6817.png" alt=""></div><img src="/packages/icons/tick_6817.png" alt="">' : '' ?></td>
      <td style="text-align: center;"><?= ($item['emails_count'] > 0) ? '<div class="popover" data-toggle="popover" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus." data-placement="right" src="/packages/icons/tick_6817.png" alt=""></div><img src="/packages/icons/tick_6817.png" alt="">' : '' ?></td>
      <td><?= $item['updated_at'] ?></td>
      <td><?= ceil( (time() - strtotime($item['domain_created'])) / (3600 * 24)) ?></td>
    </tr>
          <?
      }
  } else {
      ?>
    <tr>
      <td colspan="9" style="text-align: center; color: gray;">Список пуст</td>
    </tr>
      <?
  }
  ?>
  </tbody>
</table>

<script>
//$('.popover').popover();
</script>
  
  </div>
  
  <footer style="height: 50px;">
  
  </footer>
  
  <script type="text/javascript" src="/packages/bootstrap/js/bootstrap.min.js"></script>
  
</body>
</html>
