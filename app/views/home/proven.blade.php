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
      <span id="markers-content" style="padding-left: 5px;">
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
    
    <div class="links"></div>
    
    
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
    var slid = data_key.split('-');
    if (slid[0] == 'day'){
        _load();
        $('#export-button').attr('href', '#');
        
   
        $.ajax({
            url: '/ajax-tree?page=' + page,
            data: {
                root: data_key,
                //page: page
            },
            dataType: 'json',
            type: 'post',
            success: function(data){
                if (data.items && data.items.length > 0) {
                    var items = '';
                    var markers = [];
                    for (var i = 0; i <= data.items.length; i++) {
                        var site = data.items[i];
                        if (site) {
                            if (site.marker != '' && site.marker != null) {
                                if (markers[site.marker]) markers[site.marker]++;
                                else markers[site.marker] = 1;
                            }
                            
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
                    $('#markers-content').html('');
                    if (markers.length > 0) {
                        $.each(markers, function(index, item){
                            $('#markers-content').append('<a href="#" onclick="setMarker(\''+index+'\'); return false;">'+index+' ('+item+')</a>');
                        })
                    }
                    console.log('markers', markers);
                    
                    _unload();
                    $('#export-button').attr('href', '/export?date='+slid[1]);
                    
                    $('#main-grid tbody').html(items);
                    
                    
                    if (data.pages > 0) {
                        $('.links').html(data.paginate);
                        $('.links a').on('click', function(){
                            var toPage = $(this).attr('href').split('=')[1] * 1;
                            goPage( toPage,  data_key);
                            return false;
                        });
                    }
                    
                    
                } else _unload('Нет данных для загрузки');
            }
        });
    
    }
}


$('#atree').dynatree({
    initAjax: {
        url: '/ajax-tree',
        type: 'post'
    },
    onActivate: function(node) {
        goPage( 1,  node.data.id);
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>