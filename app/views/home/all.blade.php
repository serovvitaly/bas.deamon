<table class="table table-condensed table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>Домен</th>
      <th>Страницы</th>
      <th>Делигирован</th>
      <th>Статус</th>
      <th>Телефон1</th>
      <th>Телефон2</th>
      <th>Email</th>
      <th>Дата п. обр</th>
      <th>В статусе</th>
    </tr>
  </thead>
  <tbody>
  <?
  if (isset($sites) AND count($sites) > 0) {
      foreach ($sites AS $site) {
          ?>
    <tr>
      <td><?= $site->url ?></td>
      <td><?= $site->internal_links_count ?></td>
      <td>?</td>
      <td><?= $site->status ?></td>
      <td>?</td>
      <td>?</td>
      <td>?</td>
      <td><?= $site->updatedat ?></td>
      <td><?= $site->updatedat ?></td>
    </tr>
          <?
      }
  } else {
      ?>
    <tr>
      <td colspan="9" style="text-align: center; color: gray;">Список пуст</td>
    </tr>
      <?
  }
  ?>
  </tbody>
</table>