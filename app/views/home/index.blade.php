<fieldset style="margin-top: 20px;">
  <legend>Общая статистика</legend>
  
  <table class="table table-bordered" style="width: auto;">
    <colgroup>
      <col style="width: 200px;">
      <col style="width: 160px;">
    </colgroup>
    <tbody>
      <tr>
        <th>Всего доменов в базе</th>
        <td class="stat-load" id="data-count-0"><img src="/packages/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Доменов отвечают</th>
        <td class="stat-load" id="data-count-1"><img src="/packages/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Домены со страницами</th>
        <td class="stat-load" id="data-count-2"><img src="/packages/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Домены с контактами</th>
        <td class="stat-load" id="data-count-3"><img src="/packages/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Проверенные домены</th>
        <td class="stat-load" id="data-count-4"><img src="/packages/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
    </tbody>
  </table>
  
</fieldset>

<div>
  <button class="btn btn-primary" onclick="checkDaemon()">Проверить работу демона</button>
  <div style="margin: 20px 0;" id="deamon-content">
  
  </div>
</div>

<script>
function checkDaemon(){
    $('#deamon-content').html('<img src="/packages/icons/ajax-loader.gif" alt="загрузка">');
    $.ajax({
        url: '/check-daemon',
        dataType: 'html',
        type: 'POST',
        success: function (data){
            $('#deamon-content').html(data);
        },
        error: function(){
            $('#deamon-content').html('ошибка');
        }
    });
}
function loadCounts(){
    if (!status) status = 0;
    
    $.ajax({
        url: '/get-counts',
        dataType: 'json',
        type: 'POST',
        data: {status: status},
        success: function (data){
            $('td.stat-load').html('нет данных');
            if (data.result) {
                $.each(data.result, function(index, item){
                    $('#data-count-'+item.status).html(item.count);
                });
            }
        },
        error: function(){                              
            $('td.stat-load').html('ошибка');
        }
    });
}

loadCounts();

</script>