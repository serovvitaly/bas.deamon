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
                    console.log(data);
                }
            });
        }
    },
    //fx: { height: "toggle", duration: 200 },
});
</script>