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
      <a class="btn btn-success" href="/export?from=2013-09-01&to=2013-09-02">Экспорт в CSV</a>
    </div>
    
    @include('common.grid', array('items'=>$sites))
  </div>
        
</div>

<script>
$('#atree').dynatree({
    initAjax: {
        url: 'ajax-tree',
        type: 'post'
    },
    onLazyRead: function(node){
        
        var data = {
            root: node.data.id,
            parent_id: node.parent.data.id 
        };
        
        node.appendAjax({
          url: 'ajax-tree',
          type: 'post',
          data: data,
          debugLazyDelay: 750
        });
    },
    onActivate: function(node) {
        console.log('onActivate');
        console.log(node);
        var slid = node.data.id.split('-');
        if (slid[0] == 'day'){
            
            var day   = node.data.id.split('-')[1];
            var month = node.parent.data.id.split('-')[1];
            var year  = node.parent.parent.data.id.split('-')[1];
            if (day < 10)   day   = '0' + day; 
            if (month < 10) month = '0' + month; 
            
            $.ajax({
                url: '/ajax-tree',
                data: {
                    //root: 'day-' + day + '.' + month + '.' + year
                    root: node.data.id
                },
                dataType: 'json',
                type: 'post',
                success: function(data){
                    if (data.items && data.items.length > 0) {
                        console.log('START ITER');
                        var items = '';
                        for (var i = 0; i <= data.items.length; i++) {
                            var site = data.items[i];
                            console.log(site);
                            if (site) {
                                items += '<tr>'
                                       + '<td><a href="/checker?uid='+site.uid+'">'+site.url+'</a></td>'
                                       + '<td>'+site.meet_links+'</td>'
                                       + '<td>'+site.delegated+'</td>'
                                       + '<td>'+site.status+'</td>'
                                       + '<td style="text-align: center;"></td>'
                                       + '<td style="text-align: center;"></td>'
                                       + '<td>'+site.updated_at+'</td> '
                                       + '<td>'+site.updated_at+'</td>'
                                     + '</tr>';
                            }
                        }
                        
                        $('#main-grid tbody').html(items);
                    } else console.log('ITER NON');
                }
            });
        }
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>