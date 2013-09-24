<div class="input-append">
  <input class="span6" type="text" id="load-url" placeholder="Ведите URL для загрузки страницы" value="{{ $url }}">
  <button class="btn btn-info" type="button" onclick="loadByUrl()">Загрузить</button>
  <button class="btn btn-danger" onclick="checkContacts()">Проверить</button>
  <button class="btn btn-inverse">Сохранить и далее</button>
</div>

<div id="alerts"></div>

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