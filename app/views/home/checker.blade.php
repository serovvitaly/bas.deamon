<div class="btn-group" style="margin-bottom: 10px;">
  <a class="btn" href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/meet' ?>"><i class="icon-chevron-left"></i> К списку</a>
</div>

<div id="alerts">
<?
    if (!$url) {
?>
  <div class="alert alert-block alert-error fade in"><button type="button" class="close" data-dismiss="alert">×</button><h4 class="alert-heading">Предупреждение</h4><p>Не выбран домен для анализа, перейдите на страницу со списком доменов и нажмите на ссылку с именем домена.</p></div>
<?
    }
?>
</div>

<div class="row">
  <div class="span3">

    <table class="table table-condensed table-bordered table-striped table-hover">
      <thead>
        <tr>
          <th>URL</th>
          <th>Возраст</th>
          <th>С.ко</th>
          <th>Стр.</th>
          <th>Метка</th>
        </tr>
      </thead>
      <tbody>
      <?
      if ($pages AND count($pages) > 0) {
          foreach ($pages AS $page) {
              ?>
        <tr>
          <td><?= ($url == $page['url']) ? "<strong style='color:red'>{$page['url']}</strong>" : "<a href='/checker?uid={$uid}&url={$page['url']}'>{$page['url']}</a>" ?></td>
          <td>0</td>
          <td>0</td>
          <td><?= $page['meet_links'] ?></td>
          <td>э</td>
        </tr>
              <?
          }
      } else {
          ?>
        <tr>
          <td colspan="4" style="text-align: center; color: gray;">Список пуст</td>
        </tr>
          <?
      }
      ?>
      </tbody>
    </table>
  
  </div>
  <div class="span9">
    <h4>URL : {{ $url }}</h4>
    
    <form action="/save-data" method="POST">
        <input type="hidden" name="uid" value="{{ $uid }}">
        <input type="hidden" name="next_url" value="{{ $next_url }}">
        <div class="row-fluid">
          <div class="span4">
            <div>Телефон 1:</div>
            <input type="text" name="phones[]">
            <div>Телефон 2:</div>
            <input type="text" name="phones[]">
            <div>Телефон 3:</div>
            <input type="text" name="phones[]">
          </div>
          <div class="span4">
            <div>Email 1:</div>
            <input type="text" name="emails[]">
            <div>Email 2:</div>
            <input type="text" name="emails[]">
            <div>Email 3:</div>
            <input type="text" name="emails[]">
          </div>
          <div class="span4">
            <ul style="margin: 0; list-style: none;">
              <?
              $markers = Config::get('bas_markers');
              if (is_array($markers) AND count($markers) > 0) {
                  foreach ($markers AS $marker) {
                  ?><li><label><input name="marker" value="<?= $marker ?>" type="radio"> - <?= $marker ?></label></li><?
                  }
              }
              ?>
            </ul>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12">
            <button class="btn btn-inverse">Сохранить и далее</button>
          </div>
        </div>
    </form>

    <div>
      <iframe src="{{ $url }}" id="load-container" style="width: 100%; height: 600px; border: 1px solid #CECECE;" onload="frameLoaded()"></iframe>
    </div> 
    
  </div>
</div>




<script>
function checkContacts(){
    
    console.log( $('#load-container').contents().find("html") );
    
    return;
    
    var iframe = document.getElementById('load-container');
    iframe = (iframe.contentWindow) ? iframe.contentWindow : (iframe.contentDocument.document) ? iframe.contentDocument.document : iframe.contentDocument;
    iframe.document.open();
    var content = iframe.document.html;
    iframe.document.close();
}
function frameLoaded(){
    //checkContacts();
}
function showAlert(title, msg){
    var alert = '<div class="alert alert-block alert-error fade in"><button type="button" class="close" data-dismiss="alert">×</button><h4 class="alert-heading">'+title+'</h4><p>'+msg+'</p></div>';
    
    $('#alerts').append(alert);
}
function loadByUrl(){
    var url = $('#load-url').val();
    var iframe = document.getElementById('load-container');
    iframe = (iframe.contentWindow) ? iframe.contentWindow : (iframe.contentDocument.document) ? iframe.contentDocument.document : iframe.contentDocument;
    iframe.document.open();
    iframe.document.write('');
    $.ajax({
        url: '/get-content?url=' + url,
        type: 'POST',
        dataType: 'html',
        success: function(content){
            iframe.document.write(content);
            iframe.document.close();
        },
        error: function(jqXHR, textStatus, errorThrown){
            var data = jQuery.parseJSON(jqXHR.responseText);
            showAlert('Ошибка', data.error.message);
        }
    });
}
</script>