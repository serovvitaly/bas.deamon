<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Untitled</title>
    <link rel="stylesheet" type="text/css" href="/packages/bootstrap/css/docs.css">
    <link rel="stylesheet" type="text/css" href="/packages/bootstrap/css/bootstrap.min.css">
    
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
    </style>
    
</head>
<body>

  <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="/">Загружалко 2.0</a>
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
            </ul>
          </div>
        </div>
      </div>
  </div>

  <div class="container" style="padding-top: 20px;">
  @yield('content')
  </div>
  
</body>
</html>
