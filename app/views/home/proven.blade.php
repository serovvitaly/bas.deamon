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
function goPage(page, data_key){
    alert(page+' -- '+data_key);
}


$('#atree').dynatree({
    initAjax: {
        url: '/ajax-tree',
        type: 'post'
    },
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
                        
                        
                        if (data.pages > 0) {
                            var pages = '';
                            for(var p = 1; p <= data.pages; p++){
                                if (p == data.current_page) {
                                    pages += '<li class="active"><span>'+p+'</span></li>';
                                } else {
                                    pages += '<li><a onclick="goPage('+p+', \''+node.data.id+'\'); return false;" href="#">'+p+'</a></li>';
                                }
                                
                            }
                            $('.pagination ul').html(pages);
                        }
                        
                        
                    } else _unload('Нет данных для загрузки');
                }
            });
        }
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>