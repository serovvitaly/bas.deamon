<style>
td.pattern span{
    border: 1px solid #FFF; 
}
td.pattern span, td.pattern input{
    font-size: 22px;
    width: 97%;
    padding: 4px;
    margin: 0;
    display: block; 
}
.edit td.pattern span{
    display: none; 
}
td.pattern input{
    display: none;
}
.edit td.pattern input{
    display: block;
    color: blue;
}
</style>

<fieldset style="margin-top: 20px; margin-bottom: 50px;">
  <legend>Шаблоны поиска телефонов</legend>
  
  <div class="row">
    <div class="span6">
    
      <p style="color: gray;">Формат ввода шаблона, например: <strong style="color: black;">8 (9**) ***-**-**</strong> или <strong style="color: black;">+7 (***) ***-**-**</strong></p>
      
    <? if ($patterns AND count($patterns) > 0) { ?>
      <table class="table table-condensed table-hover table-bordered" style="width: 500px;">
        <colgroup>
          <col>
          <col style="width: 112px;">
        </colgroup>
        <? foreach ($patterns AS $pattern) { ?>
        <tr id="pattern-{{$pattern->id}}">
          <td class="pattern"><span>{{$pattern->pattern}}</span><input type="text" value="{{$pattern->pattern}}"></td>
          <td>
            <a class="ebtn" href="#" onclick="editPattern({{$pattern->id}}); return false;" title="Редактировать">ред.</a> | 
            <a href="/remove-pattern?pid={{$pattern->id}}" onclick="return confirm('Удалить шаблон?')" title="Удалить">уд.</a> | 
            <a href="#" onclick="checkReg({{$pattern->id}}); return false;" title="Проверить">пров.</a>
          </td>
        </tr>
        <? } ?>
      </table>
    <? } else { ?>
      <p><i style="color: gray;">Список пуст</i></p>
    <? } ?>

      <div id="add-pattern-form" style="display: none;">
        <input type="text">
      </div>
      
      <button class="btn btn-small" data-trigger="off" onclick="addPatternForm(this)">Добавить шаблон</button>
    
    </div>
    <div class="span3">
      <textarea id="check-reg-list" cols="" rows="" style="height: 200px;">(812) 913-43-59
8 (342) 214-40-02
+ 7 (495) 212-11-28
+7 (495) 581-91-64
+7(495) 970-29-38
+7 (916) 800-55-45
+7 (499) 390-63-71
8(495) 440-39-81
8 (3466)40-77-03
+7 (812) 946-23-99
+7 (343) 384-01-03
8(917)48-26-007
8 (4742) 71-09-01
8 (4922) 52-58-58
8 (953) 1710073
8(34345) 2-19-84</textarea>
    </div>
    <div class="span3" id="check-reg-result">результат</div>
  </div>
  
</fieldset>

<fieldset style="margin-top: 20px;">
  <legend>Управление демоном</legend>
  
  <button class="btn btn-danger" onclick="startDaemon();">Запустить ДЕМОН</button>
  
</fieldset>


<script>

function checkReg(id){
    
    if (id < 1) {
        $('#check-reg-result').html('<i style="color:red">Ошибка, неверно задан шаблон для проверки</i>');
        return false;
    }
    
    $('#check-reg-result').html('<i style="color:gray">проверка...</i>');
    
    $.ajax({
        url: '/check-reg',
        dataType: 'json',
        type: 'POST',
        data: {
            list: $('#check-reg-list').val(),
            reg_id: id
        },
        success: function(data){ console.log(data.result);
            if (data.result && data.result.length > 0) {
                var text = 'Найдено результатов: ' + data.result.length + '<ul>';
                for (var i = 0; i < data.result.length; i++) {
                    text += '<li>'+data.result[i]+'</li>';
                }
                text += '</ul>';
                
                $('#check-reg-result').html(text);
            } else {
                $('#check-reg-result').html('Ничего не найдено');
            }
        }
    });
}

function editPattern(id){
    
    var ptrn = $('#pattern-'+id);
    
    var pattern = ptrn.find('input').val().trim();
    ptrn.find('input').css('border-color', '');
    if (pattern == '') {
        ptrn.find('input').css('border-color', 'red');
        return;
    }
    
    ptrn.find('span').text(pattern);
    
    //$('tr.edit').find('.ebtn').attr('title', 'Редактировать').text('ред.');
    //$('tr.edit').removeClass('edit');
    
    if (ptrn.hasClass('edit')) {
        ptrn.removeClass('edit');
        ptrn.find('.ebtn').attr('title', 'Редактировать').text('ред.');
        $.ajax({
            url: '/save-pattern',
            dataType: 'json',
            type: 'POST',
            data: {
                pattern: pattern,
                pid: id
            },
            success: function(data){
                if (data.success === true) {
                    //window.location = window.location;
                }
            }
        });
    } else {
        ptrn.addClass('edit');
        ptrn.find('.ebtn').attr('title', 'Сохранить').text('сох.');
    }
    
}
function addPatternForm(el){
    $('tr.edit').find('.ebtn').attr('title', 'Редактировать').text('ред.');
    $('tr.edit').removeClass('edit');
    
    $('#add-pattern-form input').css('border-color', '');
    if ( $(el).attr('data-trigger') == 'off' ) {
        $('#add-pattern-form').slideDown(100);
        $(el).html('Сохранить шаблон');
        $(el).attr('data-trigger', 'on');
    } else {
        savePatternForm(el);
    }
}
function savePatternForm(el){
    $('tr.edit').find('.ebtn').attr('title', 'Редактировать').text('ред.');
    $('tr.edit').removeClass('edit');
    
    var pattern = $('#add-pattern-form input').val().trim();
    $('#add-pattern-form input').css('border-color', '');
    if (pattern == '') {
        $('#add-pattern-form input').css('border-color', 'red');
        return;
    }
    
    $.ajax({
        url: '/save-pattern',
        dataType: 'json',
        type: 'POST',
        data: {
            pattern: pattern
        },
        success: function(data){
            if (data.success === true) {
                window.location = window.location;
            }
        }
    });
    
    $('#add-pattern-form').slideUp(100);
    $(el).html('Добавить шаблон');
    $(el).attr('data-trigger', 'off');
}
function startDaemon(){
    
    if (!confirm('Подтвердить запуск демона.')) {
        return;
    }
    
    post('/start-daemon', {}, function(data){
        if (data.success === true) {                            
            //                  
        }
    });
}
</script>