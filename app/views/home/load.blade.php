
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/vendor/jquery.ui.widget.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.iframe-transport.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.fileupload.js"></script>


<script>
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $('#progress').html('Загрузка завершена');
            console.log(data);
            $.each(data.result.files, function (index, file) {
                $('<p/>').text(file.name).appendTo(document.body);
            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress').html(progress + '%');
        }
    });
});
</script>

<span class="btn btn-success fileinput-button">
    <i class="icon-download-alt icon-white"></i>
    <span>Загрузить файл...</span>
    <input style="position:absolute;margin:-25px 0 0 -75px;opacity:0;filter:alpha(opacity=0);font-size: 18px;cursor:pointer;transform:translate(-300px, 0) scale(4);" id="fileupload" type="file" name="files[]" data-url="/upload">
</span>

<span style="padding-left: 10px; font-size: 14px;" id="progress"></span>

<fieldset style="margin-top: 20px;">
  <legend>Статистика</legend>
  
  <table class="table table-bordered table-striped">
    <thead><tr>
      <th>Имя файла</th>
      <th>Дата загрузки</th>
      <th>Объем файла</th>
      <th>Найдено ссылок</th>
      <th>Добавлено в базу</th>
    </tr></thead>
  </table>
  
</fieldset>