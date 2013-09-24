<div class="btn-group" style="margin-bottom: 10px;">
  <a class="btn" href="<?= $_SERVER['HTTP_REFERER'] ?>"><i class="icon-chevron-left"></i> К списку</a>
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

<h4>URL : {{ $url }}</h4>

<table class="table table-condensed table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>URL</th>
      <th>Код ответа</th>
      <th>Телефоны</th>
      <th>Email-ы</th>
    </tr>
  </thead>
  <tbody>
  <?
  $phones = array(); 
  $emails = array(); 
  if ($pages AND count($pages) > 0) {
      foreach ($pages AS $page) {
          if ($url == $page['url']) {
              $phones = $page['phones'];
              $emails = $page['emails']; 
          }
          ?>
    <tr>
      <td><?= ($url == $page['url']) ? "<strong style='color:red'>{$page['url']}</strong>" : "<a href='/checker?uid={$uid}&url={$page['url']}'>{$page['url']}</a>" ?></td>
      <td><?= $page['http_code'] ?></td>
      <td><?= implode(',', $page['phones']) ?></td>
      <td><?= implode(',', $page['emails']) ?></td>
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

<form action="">
    <div class="row">
      <div class="span6">
        <fieldset>
          <legend>Телефоны через запятую</legend>
          <textarea style="width: 98%; height: 60px;" name="phones" cols="" rows=""><?= implode(',', $phones) ?></textarea>
        </fieldset>
      </div>
      <div class="span6">
        <fieldset>
          <legend>Email-ы через запятую</legend>
          <textarea style="width: 98%; height: 60px;" name="emails" cols="" rows=""><?= implode(',', $emails) ?></textarea>
        </fieldset>
      </div>
      <div class="span12">
        <button class="btn btn-inverse">Сохранить и далее</button>
      </div>
    </div>
</form>

<div>
  <iframe src="{{ $url }}" id="load-container" style="width: 100%; height: 600px; border: 1px solid #CECECE;" onload="frameLoaded()"></iframe>
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