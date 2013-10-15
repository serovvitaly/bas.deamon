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
        <td id="data-all"><img src="/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Доменов отвечают</th>
        <td id="data-s1"><img src="/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Домены со страницами</th>
        <td id="data-s2"><img src="/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Домены с контактами</th>
        <td id="data-s3"><img src="/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
      <tr>
        <th>Проверенные домены</th>
        <td id="data-s4"><img src="/icons/ajax-loader.gif" alt="загрузка"></td>
      </tr>
    </tbody>
  </table>
  
</fieldset>

<script>
function getCount(target, status){
    if (!status) status = 0;
    
    $.ajax({
        url: '/get-count',
        dataType: 'html',
        type: 'POST',
        data: {status: status},
        success: function (data){
            $(target).html(data);
        },
        error: function(){
            $(target).html('ошибка');
        }
    });
}

getCount('#data-all');
getCount('#data-s1', 1);
getCount('#data-s2', 2);
getCount('#data-s3', 3);
getCount('#data-s4', 4);

</script>