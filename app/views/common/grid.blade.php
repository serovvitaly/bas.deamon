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
  if (isset($items) AND count($items) > 0) {
      foreach ($items AS $item) {
          ?>
    <tr>
      <td><a href="/checker?uid=<?= $item->id ?>"><?= $item->url ?></a></td>
      <td><?= $item->internal_links_count ?></td>
      <td><?= $item->delegated ?></td>
      <td><?= $item->status ?></td>
      <td>?</td>
      <td>?</td>
      <td>?</td>
      <td><?= $item->updated_at ?></td>
      <td><?= $item->updated_at ?></td>
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

<?php echo $items->links(); ?>