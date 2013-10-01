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
        node.appendAjax({
          url: 'ajax-tree',
          type: 'post',
          data: {
              root: node.data.id
          },
          debugLazyDelay: 750
        });
    },
    onActivate: function(node) {
        var slid = node.data.id.split('-');
        if (slid[0] == 'day'){
            $.ajax({
                url: '/ajax-tree',
                data: {
                    root: node.data.id
                },
                dataType: 'json',
                type: 'post',
                success: function(data){
                    if (typeof data == 'array' && data.length > 0) {
                        var items = '';
                        for (var i = 0; i <= data.length; i++) {
                            var site = data[i];
                            items += '<tr>'
                                   + '<td><a href="/checker?uid='+site.id+'">'+site.url+'</a></td>'
                                   + '<td>'+site.meet_links+'</td>'
                                   + '<td>'+site.delegated+'</td>'
                                   + '<td>'+site.status+'</td>'
                                   + '<td style="text-align: center;"></td>'
                                   + '<td style="text-align: center;"></td>'
                                   + '<td>'+site.updated_at+'</td> '
                                   + '<td>'+site.updated_at+'</td>'
                                 + '</tr>';
                        }
                        
                        $('#main-grid tbody').html();
                    }
                }
            });
        }
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>