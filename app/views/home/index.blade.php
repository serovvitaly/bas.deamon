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
        <td>{{ $count_all }}</td>
      </tr>
      <tr>
        <th>Доменов отвечают</th>
        <td>{{ $count_1 }}</td>
      </tr>
      <tr>
        <th>Домены со страницами</th>
        <td>{{ $count_2 }}</td>
      </tr>
      <tr>
        <th>Домены с контактами</th>
        <td>{{ $count_3 }}</td>
      </tr>
      <tr>
        <th>Проверенные домены</th>
        <td>{{ $count_4 }}</td>
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
</script>