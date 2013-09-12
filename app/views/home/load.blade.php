
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/vendor/jquery.ui.widget.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.iframe-transport.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.fileupload.js"></script>


<script>
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        add: function (e, data) {
            var file = data.files[0];
            $('#table-file-list tbody').prepend('<tr>'
                    + '<td>' + file.name + '</td>'
                    + '<td></td>'
                    + '<td class="during" style="text-align: center;"><div class="uloader"><div class="ufiller" style="width:0%"></div><div class="ucounter">0%</div></div></td>'
                    + '<td style="text-align: center;"></td>'
                    + '<td style="text-align: right;">' + Math.ceil( file.size / 1000000 ) + ' МБ</td>'
                    + '<td></td>'
                    + '<td></td>'
                  + '</tr>'
            );
            
            data.submit();
        },
        done: function (e, data) {
            $('#table-file-list .during').html('загружен');
            $('#table-file-list .during').removeClass('during');
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);            
            $('#table-file-list .during .ufiller').css('width', progress + '%');
            $('#table-file-list .during .ucounter').html(progress + '%');
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
  
  <table id="table-file-list" class="table table-bordered table-striped">
    <thead><tr>
      <th style="width: 200px;">Имя файла</th>
      <th>Дата загрузки</th>
      <th style="text-align: center; width: 100px;">Загрузка</th>
      <th style="text-align: center;">Время загрузки</th>
      <th>Объем файла</th>
      <th>Найдено ссылок</th>
      <th>Добавлено в базу</th>
    </tr></thead>
    <tbody>
    @foreach ($files as $file)
      <tr>
        <td>{{ $file->name }}</td>
        <td>{{ $file->created_at }}</td>
        <td style="text-align: center;">загружен</td>
        <td style="text-align: center;">{{ $file->load_stop - $file->load_start  }} мин.</td>
        <td style="text-align: right;">{{ ceil($file->size / 1000000) }} МБ</td>
        <td></td>
        <td></td>
      </tr>
    @endforeach
    </tbody>
  </table>
  
</fieldset>