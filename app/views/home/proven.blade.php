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
        <?
            for ($year = 2013; $year <= intval(date('Y')); $year++) {
            ?>
        <li id="year-<?= $year ?>" class="lazy folder"><?= $year ?></li>
            <?
            }
        ?>
            <li id="key1" title="Look, a tool tip!">item1 with key and tooltip
            <li id="key2" class="selected">item2: selected on init
            <li id="key3" class="folder">Folder with some children
                <ul>
                    <li id="key3.1">Sub-item 3.1
                    <li id="key3.2">Sub-item 3.2
                </ul>

            <li id="key4" class="expanded">Document with some children (expanded on init)
                <ul>
                    <li id="key4.1">Sub-item 4.1
                    <li id="key4.2">Sub-item 4.2
                </ul>

            <li id="key5" class="lazy folder">Lazy folder
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
    //fx: { height: "toggle", duration: 200 },
});
</script>