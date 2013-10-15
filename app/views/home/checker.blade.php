<!--div class="btn-group" style="margin-bottom: 10px;">
  <a class="btn" href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/meet' ?>"><i class="icon-chevron-left"></i> К списку</a>
</div-->

<div id="alerts">
</div>

<div class="row">
  <div class="span3" style="overflow: auto;">

    <table class="table table-condensed table-bordered table-striped table-hover check-table">
      <thead>
        <tr>
          <th style="width: 200px;">URL</th>
          <th style="width: 40px;">Метка</th>
        </tr>
      </thead>
      <tbody>
      <?
      $sites[] = array(
          'url' => 'ewrqw4ytytryy',
          'id' => 9,
          'marker' => 444,
          'domain_created' => 3333,
          'updated_at' => 444,
          'meet_links' => 5555,
          
      );
      if ($sites AND count($sites) > 0) {
          foreach ($sites AS $site) {
              ?>
        <tr>
          <td title="<?= $site['url'] ?>"><?= ($url == $site['url']) ? "<strong style='color:red'>{$site['url']}</strong>" : "<a href='/checker?uid={$site['id']}'>{$site['url']}</a>" ?>
            <div class="popoverdzen" style="display: none;">
              <table>
                <tr>
                  <td>Возраст домена:</td>
                  <td><?= round( (time() - strtotime($site['domain_created'])) / (3600 * 24 * 365.259636), 1 ) ?> г.</td>
                </tr>
                <tr>
                  <td>Найдены контакты:</td>
                  <td><?= ceil((time() - strtotime($site['updated_at'])) / (3600*24)) ?> дн.</td>
                </tr>
                <tr>
                  <td>Страниц найдено:</td>
                  <td><?= $site['meet_links'] ?></td>
                </tr>
              </table>
            </div>
          </td>
          <td title="<?= $site['marker'] ?>"><?= $site['marker'] ?></td>
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
    
    <?php echo $sites->links(); ?>
  
  </div>
  <div class="span9">
    <h4>URL : {{ $url }}</h4>
    
    <form action="/save-data" method="POST">
        <input type="hidden" name="uid" value="{{ $uid }}">
        <input type="hidden" name="next_uid" value="{{ $next_uid }}">
        <div class="row-fluid">
          <div class="span4">
            <div>Телефон 1:</div>
            <input type="text" name="phones[]" value="<?= isset($phones[0]) ? $phones[0] : '' ?>">
            <div>Телефон 2:</div>
            <input type="text" name="phones[]" value="<?= isset($phones[1]) ? $phones[1] : '' ?>">
            <div>Телефон 3:</div>
            <input type="text" name="phones[]" value="<?= isset($phones[2]) ? $phones[2] : '' ?>">
          </div>
          <div class="span4">
            <div>Email 1:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[0]) ? $emails[0] : '' ?>">
            <div>Email 2:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[1]) ? $emails[1] : '' ?>">
            <div>Email 3:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[2]) ? $emails[2] : '' ?>">
          </div>
          <div class="span4">
            <ul style="margin: 0; list-style: none;">
              <?
              $markers = Config::get('bas_markers');
              if (is_array($markers) AND count($markers) > 0) {
                  foreach ($markers AS $m_name) {
                      $checked = ($marker == $m_name) ? ' checked="checked"' : '';
                  ?><li><label><input<?= $checked ?> name="marker" value="<?= $m_name ?>" type="radio"> - <?= $m_name ?></label></li><?
                  }
              }
              ?>
            </ul>
            <button style="margin-top: 10px;" class="btn btn-inverse">Сохранить и далее</button>
          </div>
        </div>
        <div class="row-fluid">
          <div class="span12">
            
          </div>
        </div>
    </form>

    <div>
      <iframe sandbox src="{{ $url }}" id="load-container" style="width: 100%; height: 600px; border: 1px solid #CECECE;" onload="frameLoaded()"></iframe>
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

$('.check-table tr').on('mouseover', function(){
    $('.check-table .popoverdzen').hide();
    $(this).find('.popoverdzen').show();
}).on('mouseout', function(){
    $(this).find('.popoverdzen').hide();
});

</script>