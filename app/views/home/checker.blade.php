<!--div class="btn-group" style="margin-bottom: 10px;">
  <a class="btn" href="<?= isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/meet' ?>"><i class="icon-chevron-left"></i> К списку</a>
</div-->

<div id="alerts">
</div>

<div class="row-fluid">
  <div class="span3" style="overflow: auto;">

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
  <div class="span9">
    <h4>URL : {{ $url }}</h4>
    
    <form action="/save-data" method="POST" style="margin: 0;">
        <input type="hidden" name="uid" value="{{ $uid }}">
        <input type="hidden" name="next_uid" value="{{ $next_uid }}"> 
        <div>
          <div>
          <div style="display: inline-block; width: 40%;">
            <input style="display: inline-block; width: 28%;" type="text" name="phones[]" value="<?= isset($phones[0]) ? trim($phones[0]) : '' ?>">
            <input style="display: inline-block; width: 28%;" type="text" name="phones[]" value="<?= isset($phones[1]) ? trim($phones[1]) : '' ?>">
            <input style="display: inline-block; width: 28%;" type="text" name="phones[]" value="<?= isset($phones[2]) ? trim($phones[2]) : '' ?>">
          </div>
          <div style="display: inline-block; width: 40%;">
            <input style="display: inline-block; width: 28%;" type="text" name="emails[]" value="<?= isset($emails[0]) ? trim($emails[0]) : '' ?>">
            <input style="display: inline-block; width: 28%;" type="text" name="emails[]" value="<?= isset($emails[1]) ? trim($emails[1]) : '' ?>">
            <input style="display: inline-block; width: 28%;" type="text" name="emails[]" value="<?= isset($emails[2]) ? trim($emails[2]) : '' ?>">
          </div>
          <a style="display: inline-block; margin-bottom: 10px;" class="btn btn-success" href="#" onclick="checkAll(); return false;">Проверить</a>
          </div>
          <div style="padding: 10px 0;">
              <?
              $markers = Config::get('bas_markers');
              if (is_array($markers) AND count($markers) > 0) {
                  foreach ($markers AS $m_name) {
                      $checked = ($marker == $m_name) ? ' btn-primary' : '';
                  ?><button style="margin-right: 5px;" class="btn btn-small<?= $checked ?>" name="marker" value="<?= $m_name ?>"><?= $m_name ?></button><?
                  }
              }
              ?>
              <button style="margin-top: 10px;" class="btn btn-inverse btn-small">Сохранить и далее</button>
          </div>
        </div>
    </form>

    <div>
      <iframe sandbox src="{{ $url }}" id="load-container" style="width: 100%; height: 600px; border: 1px solid #CECECE;" onload="frameLoaded()"></iframe>
    </div> 
    
  </div>
</div>




<script>
function checkAll(){
    checkPhones();
    checkEmails();
}
function checkPhones(){
    
    $('#check-phones-content').html('<i>выполнение операции...</i>');
    $.ajax({
        url: '/load-url-content',
        dataType: 'json',
        type: 'POST',
        data: {
            url: $('#load-container').attr('src'),
            uid: '{{ $uid }}',
            type: 'phone'
        },
        success: function(data){
            if (data.result !== null && data.result.length > 0) {
                var emptyFields = $('input[name="phones[]"]:not([value!=""])');
                for (var i = 0; i < 4; i++) {                    
                    if (data.result[i]) {
                        $(emptyFields[i]).val(data.result[i]);
                    }
                }
                $('#check-phones-content').html('');
            } else {
                $('#check-phones-content').html('ничего не найдено');
                setTimeout(function(){
                    $('#check-phones-content').fadeOut(3000, function(){
                        $('#check-phones-content').html('');
                        $('#check-phones-content').show();
                    });
                }, 3000);
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
            uid: '{{ $uid }}',
            type: 'email'
        },
        success: function(data){
            if (data.result !== null && data.result.length > 0) {
                var emptyFields = $('input[name="emails[]"]:not([value!=""])');
                console.log(emptyFields);
                for (var i = 0; i < 4; i++) {
                    if (data.result[i]) {
                        $(emptyFields[i]).val(data.result[i]);
                    }
                }
                $('#check-emails-content').html('');
            } else {
                $('#check-emails-content').html('ничего не найдено');
                setTimeout(function(){
                    $('#check-emails-content').fadeOut(3000, function(){
                        $('#check-emails-content').html('');
                        $('#check-emails-content').show();    
                    });
                    
                }, 3000);
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
function setIframeHeight(){
    $('#load-container').height( $(document).height() - 316 );
}

$('.check-table tr').on('mouseover', function(){
    $('.check-table .popoverdzen').hide();
    $(this).find('.popoverdzen').show();
}).on('mouseout', function(){
    $(this).find('.popoverdzen').hide();
});

setIframeHeight();

$(window).resize(function(){
    setIframeHeight();
});

</script>