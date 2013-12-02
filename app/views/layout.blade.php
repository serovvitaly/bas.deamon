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
              <li{{ (Request::is('/')       ? ' class="active"' : '') }}><a href="/"><i class="icon-home icon-white" title="Главная"></i></a></li>
              <li{{ (Request::is('load')    ? ' class="active"' : '') }}><a href="/load">Загрузка</a></li>
              <li{{ (Request::is('all')     ? ' class="active"' : '') }}><a href="/all">Все</a></li>
              <li{{ (Request::is('meet')    ? ' class="active"' : '') }}><a href="/meet">Отвечают</a></li>
              <li{{ (Request::is('pages')   ? ' class="active"' : '') }}><a href="/pages">Есть странцы</a></li>
              <li{{ (Request::is('conts')   ? ' class="active"' : '') }}><a href="/conts">Есть контакты</a></li>
              <li{{ (Request::is('checker') ? ' class="active"' : '') }}><a href="/checker">Проверка</a></li>
              <li{{ (Request::is('proven')  ? ' class="active"' : '') }}><a href="/proven">Проверенные</a></li>
              <li{{ (Request::is('daemons') ? ' class="active"' : '') }}><a href="/daemons">Управление</a></li>
            </ul>
          </div>
        </div>
      </div>
  </div>

  <div class="container" style="padding-top: 20px;">
  @yield('content')
  </div>
  
  <footer style="height: 10px;">
  
  </footer>
  
  <script type="text/javascript" src="/packages/bootstrap/js/bootstrap.min.js"></script>
  
</body>
</html>
