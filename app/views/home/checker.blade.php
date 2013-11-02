<!--div class="btn-group" style="margin-bottom: 10px;">
  <a class="btn" href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/meet' ?>"><i class="icon-chevron-left"></i> К списку</a>
</div-->

<div id="alerts">
</div>

<div class="row-fluid">
  <div class="span4" style="overflow: auto;">

    <div class="statuses-filter">
      <form action="" method="GET">
        <label style="display: inline-block; padding-right: 15px;"><input<?= in_array(1, $filter_status) ? ' checked="checked"' : '' ?> type="checkbox" name="filter_status[]" value="1"> - отвечают</label>
        <label style="display: inline-block; padding-right: 15px;"><input<?= in_array(2, $filter_status) ? ' checked="checked"' : '' ?> type="checkbox" name="filter_status[]" value="2"> - есть страницы</label>
        <label style="display: inline-block; padding-right: 15px;"><input<?= in_array(3, $filter_status) ? ' checked="checked"' : '' ?> type="checkbox" name="filter_status[]" value="3"> - есть контакты</label>
      </form>
    </div>
    <script>
      $('.statuses-filter input').change(function(){
          $('.statuses-filter form').submit();
      });
    </script>
  
    <table class="table table-condensed table-bordered table-striped table-hover check-table">
      <thead>
        <tr>
          <th style="width: 200px;">URL</th>
          <th style="width: 40px;">Метка</th>
        </tr>
      </thead>
      <tbody>
      <?
      if ($sites AND count($sites) > 0) {
          foreach ($sites AS $site) {
              ?>
        <tr>
          <td><?= ($url == $site['url']) ? "<strong style='color:red'>{$site['url']}</strong>" : "<a href='/checker?uid={$site['id']}&filter_status[]=".implode('&filter_status[]=', $filter_status)."'>{$site['url']}</a>" ?>
            <div class="popoverdzen" style="display: none;">
              <table>
                <tr>
                  <td>Возраст домена:</td>
                  <td><?= ceil((time() - strtotime($site['domain_created'])) / (3600 * 24)) ?> дн.</td>
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
          <td><?= $site['marker'] ?></td>
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
  <div class="span8">
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
            <br>
            <a class="btn btn-success" href="#" onclick="checkPhones(); return false;">Проверить</a>
            <div id="check-phones-content"></div>
          </div>
          <div class="span4">
            <div>Email 1:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[0]) ? $emails[0] : '' ?>">
            <div>Email 2:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[1]) ? $emails[1] : '' ?>">
            <div>Email 3:</div>
            <input type="text" name="emails[]" value="<?= isset($emails[2]) ? $emails[2] : '' ?>">
            <br>
            <a class="btn btn-success" href="#" onclick="checkEmails(); return false;">Проверить</a>
            <div id="check-emails-content"></div>
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
      <iframe sandbox  name="load_container" src="{{ $url }}" id="load-container" style="width: 100%; height: 600px; border: 1px solid #CECECE;" onload="frameLoaded()"></iframe>
    </div> 
    
  </div>
</div>




<script>
function checkPhones(){
    
    console.log( $('#load-container').contents() );
    console.log( document.getElementById("load-container").documentWindow );
    
    $('#check-phones-content').html('<i>выполнение операции...</i>');
    $.ajax({
        url: '/load-url-content',
        dataType: 'json',
        type: 'POST',
        data: {
            url: $('#load-container').attr('src'),
            type: 'phone'
        },
        success: function(data){
            //console.log(data);
            if (data.result !== null && data.result.length > 0) {
                var lines = '';
                for (var i = 0; i < data.result.length; i++) {
                    lines += '<p>'+data.result[i]+'</p>';
                }
                $('#check-phones-content').html(lines);
            } else {
                $('#check-phones-content').html('ничего не найдено');
            }
        }
    });
}
function checkEmails(){
    $('#check-emails-content').html('<i>выполнение операции...</i>');
    $.ajax({
        url: '/load-url-content',
        dataType: 'json',
        type: 'POST',
        data: {
            url: $('#load-container').attr('src'),
            type: 'email'
        },
        success: function(data){
            //console.log(data);
            if (data.result !== null && data.result.length > 0) {
                var lines = '';
                for (var i = 0; i < data.result.length; i++) {
                    lines += '<p>'+data.result[i]+'</p>';
                }
                $('#check-emails-content').html(lines);
            } else {
                $('#check-emails-content').html('ничего не найдено');
            }
        }
    });
}
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