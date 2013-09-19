


<script>
var inprocessData = [];

var periodic = $.periodic({
    period: 5000,
    decay:  1
}, function(){
    post('/smartupdater', {ids: inprocessData}, function(data){
        if (data.success === true && data.result && data.result.length > 0) {
            $.each(data.result, function(index, item){
                var proc = Math.ceil( item.number_lines_proc / item.number_lines * 100 );
                $('#table-file-list #ufile-'+item.id+' .during').html('<div class="uloader blue"><div class="ufiller" style="width:'+proc+'%"></div><div class="ucounter">'+proc+'%</div></div>');
                
                if (proc >= 100) {
                    $('#table-file-list #ufile-'+item.id+' .during').html('обработан');
                    delete inprocessData[item.id];
                    if (inprocessData.length < 1) {
                        periodic.cancel();
                    } else {
                        periodic.reset();
                    }
                }
            });
        }
        
        if (inprocessData.length < 1) {
            periodic.cancel();
        }
    });
});
periodic.cancel();

function inprocess(uid){
    inprocessData.push(uid);
    periodic.reset();
}

function doProcess(uid){
    if (uid < 1) return;
    
    post('/process', {id: uid}, function(data){
        if (data.success === true) {                            
            inprocess(uid);                    
        }
    });
}

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
                    post('/process', {id: uid}, function(data){
                        if (data.success === true) {                            
                            inprocess(uid);                    
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
    
    $('#table-file-list tr').each(function(item){
        var uid = $(this).attr('data-uid');
        $(this).find('.during').append('<div class="btn-group" style="margin-left:10px">'
                +'<button class="btn btn-mini dropdown-toggle" data-toggle="dropdown"><i class="icon-align-justify"></i> <span class="caret"></span></button>'
                +'<ul class="dropdown-menu">'
                  +'<li><a href="#" onclick="doProcess('+uid+'); return false;">Обработать</a></li>'
                +'</ul>'
              +'</div>');
    });
    
    $('#table-file-list .inprocess').each(function(item){
        inprocess( $(this).attr('data-uid') );
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
  <legend>Список загруженных файлов</legend>
  
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
      <tr data-uid="{{ $file->id }}" id="ufile-{{ $file->id }}"@if ($file->number_lines_proc < $file->number_lines) class="inprocess" @endif>
        <td>{{ $file->name }}</td>
        <td>{{ $file->created_at }}</td>
        <td style="text-align: center; width: 120px;" class="during">{{ $statuses[ $file->status ] }}</td>
        <!--td style="text-align: center;">{{ $file->load_stop - $file->load_start  }} мин.</td-->
        <td style="text-align: right;">~ {{ ceil($file->size / 1000000) }} МБ</td>
        <td style="text-align: right;">{{ $file->number_lines }}</td>
        <td></td>
      </tr>
    @endforeach
    </tbody>
  </table>
  
</fieldset>

<script src="/packages/bootstrap/js/bootstrap.min.js"></script>