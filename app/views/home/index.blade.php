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
        <td>{{ $domains['all'] }}</td>
      </tr>
      <tr>
        <th>Доменов отвечают</th>
        <td>{{ $domains['meet'] }}</td>
      </tr>
      <tr>
        <th>Домены со страницами</th>
        <td>{{ $domains['pages'] }}</td>
      </tr>
      <tr>
        <th>Домены с контактами</th>
        <td>{{ $domains['conts'] }}</td>
      </tr>
      <tr>
        <th>Проверенные домены</th>
        <td>{{ $domains['proven'] }}</td>
      </tr>
    </tbody>
  </table>
  
</fieldset>