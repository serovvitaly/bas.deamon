
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/vendor/jquery.ui.widget.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.iframe-transport.js"></script>
<script src="/packages/jQuery-File-Upload-8.8.5/js/jquery.fileupload.js"></script>

<script src="/packages/Smartupdater/smartupdater.js"></script>

<script>
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        add: function (e, mix) {
            var file = mix.files[0];
            post('/fadd', {}, function(data){
                if (data.success === true && data.id && data.id > 0) {
                    $('#table-file-list tbody').prepend('<tr data-uid="'+data.id+'" id="ufile-'+data.id+'">'
                            + '<td>' + file.name + '</td>'
                            + '<td></td>'
                            + '<td class="during" style="text-align: center;"><div class="uloader"><div class="ufiller" style="width:0%"></div><div class="ucounter">0%</div></div></td>'
                            + '<!--td style="text-align: center;"></td-->'
                            + '<td style="text-align: right;">~ ' + Math.ceil( file.size / 1000000 ) + ' МБ</td>'
                            + '<td></td>'
                            + '<td></td>'
                          + '</tr>'
                    );
                    
                    mix.formData = {
                        id: data.id
                    };
                                    
                    mix.submit();
                }
            });            
        },
        done: function (e, data) {
            var uid = data.formData.id;
            $('#table-file-list #ufile-'+uid+' .during').html('загружен');
            $('#table-file-list #ufile-'+uid+' .during').html('<em>извлечение...</em>');
            post('/unpack', {
                id: uid
            }, function(data){
                if (data.success === true) {
                    $('#table-file-list #ufile-'+uid+' .during').html('извлечен');
                    
                    $('#table-file-list #ufile-'+uid).smartupdater({
                        url : "/smartupdater",
                        type: 'POST',
                        data: {
                            id: uid
                        },
                        dataType: 'json',
                        minTimeout: 5000,
                    }, function (data) {
                        if (data.number_lines_proc >= data.number_lines) {
                            $('#table-file-list #ufile-'+uid).smartupdaterStop();
                        }
                    });
                    
                }
            });
        },
        progressall: function (e, data) {
            //var uid = data.formData.id;
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

<?
    $statuses = array(
        0 => 'пустой',
        1 => 'загружен',
        2 => 'извлечен',
        3 => 'обработан'
    );
?>

<fieldset style="margin-top: 20px;">
  <legend>Статистика</legend>
  
  <table id="table-file-list" class="table table-bordered table-striped table-condensed">
    <thead><tr>
      <th style="width: 200px;">Имя файла</th>
      <th>Дата загрузки</th>
      <th style="text-align: center; width: 100px;">Статус</th>
      <!--th style="text-align: center;">Время загрузки</th-->
      <th>Размер архива</th>
      <th>Найдено ссылок</th>
      <th>Добавлено в базу</th>
    </tr></thead>
    <tbody>
    @foreach ($files as $file)
      <tr data-uid="{{ $file->id }}" id="ufile-{{ $file->id }}">
        <td>{{ $file->name }}</td>
        <td>{{ $file->created_at }}</td>
        <td style="text-align: center;">{{ $statuses[ $file->status ] }}</td>
        <!--td style="text-align: center;">{{ $file->load_stop - $file->load_start  }} мин.</td-->
        <td style="text-align: right;">~ {{ ceil($file->size / 1000000) }} МБ</td>
        <td></td>
        <td></td>
      </tr>
    @endforeach
    </tbody>
  </table>
  
</fieldset>