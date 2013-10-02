<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>

<script type="text/javascript" src="/packages/dynatree/src/jquery.dynatree.js"></script>
<link rel="stylesheet" type="text/css" href="/packages/dynatree/src/skin/ui.dynatree.css">

<div class="row">

  <div class="span3">
      <ul id="atree33" style="display: none;">
        <?
            for ($year = 2013; $year <= intval(date('Y')); $year++) {
            ?>
        <li id="year-<?= $year ?>" class="hasChildren">
            <span><?= $year ?></span>
            <ul>
                <li><span class="placeholder">&nbsp;</span></li>
            </ul>
        </li>
            <?
            }
        ?>
    </ul>
    
    <div id="atree">
      <ul>
      </ul>
    </div>
    
  </div>
    
  
  <div class="span9">
  
    <div style="margin-bottom: 10px;">
      <a class="btn btn-success" id="export-button" href="#">Экспорт в CSV</a>
      <span id="informer">
        <i class="loader"></i>
        <span class="content"></span>
      </span>
    </div>
    
    <table id="main-grid" class="table table-condensed table-bordered table-striped table-hover">
      <thead>
        <tr>
          <th>Домен</th>
          <th style="width: 88px;">Страницы</th>
          <th style="width: 88px;">Делигирован</th>
          <th style="width: 88px;">Статус</th>
          <th>Телефоны</th>
          <th>Email-ы</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
    
    <div class="pagination">
        <ul>
            <li class="disabled"><span>«</span></li>
            <li class="active"><span>1</span></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=2">2</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=3">3</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=4">4</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=5">5</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=6">6</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=7">7</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=8">8</a></li>
            <li class="disabled"><span>...</span></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=96409">96409</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=96410">96410</a></li>
            <li><a href="http://x1.zwrk00.fvds.ru/all?page=2">»</a></li>
            </ul>
    </div>
    
    
  </div>
        
</div>

<script>
function _load(text){
    $('#informer .loader').show();
    $('#informer .content').html(text);
}
function _unload(text){
    $('#informer .loader').hide();
    $('#informer .content').html(text);
}


$('#atree').dynatree({
    onActivate: function(node) {
        var slid = node.data.id.split('-');
        if (slid[0] == 'day'){
            _load();
            $('#export-button').attr('href', '#');
            $.ajax({
                url: '/ajax-tree',
                data: {
                    root: node.data.id
                },
                dataType: 'json',
                type: 'post',
                success: function(data){
                    if (data.items && data.items.length > 0) {
                        var items = '';
                        for (var i = 0; i <= data.items.length; i++) {
                            var site = data.items[i];
                            if (site) {
                                items += '<tr>'
                                       + '<td><a href="/checker?uid='+site.uid+'">'+site.url+'</a></td>'
                                       + '<td>'+site.meet_links+'</td>'
                                       + '<td class="cls-small">'+site.delegated+'</td>'
                                       + '<td>'+site.status+'</td>'
                                       + '<td style="text-align: center;">'+site.phones+'</td>'
                                       + '<td style="text-align: center;">'+site.emails+'</td>'
                                       //+ '<td>'+site.updated_at+'</td> '
                                       //+ '<td>'+site.updated_at+'</td>'
                                     + '</tr>';
                            }
                        }
                        
                        _unload();
                        $('#export-button').attr('href', '/export?date='+slid[1]);
                        
                        $('#main-grid tbody').html(items);
                    } else _unload('Нет данных для загрузки');
                }
            });
        }
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>