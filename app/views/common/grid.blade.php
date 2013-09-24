<table class="table table-condensed table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>Домен</th>
      <th>Страницы</th>
      <th>Делигирован</th>
      <th style="width: 86px;">Статус</th>
      <th>Телефоны</th>
      <th>Email-ы</th>
      <th>Дата п. обр</th>
      <th>В статусе</th>
    </tr>
  </thead>
  <tbody>
  <?
  
  $delegated = array(
      'DELEGATED' => 'ДА',
      'NOT DELEGATED' => 'НЕТ',
  );
  
  $statuses = array(
      0 => 'не обработан',
      1 => 'отвечает',
      2 => 'есть страницы',
      3 => 'есть контакты',
      4 => 'проверен',
  );
  
  if (isset($items) AND count($items) > 0) {
      foreach ($items AS $item) {
          ?>
    <tr>
      <td><a href="/checker?uid=<?= $item->id ?>"><?= $item->url ?></a></td>
      <td><?= $item->internal_links_count ?></td>
      <td><?= $delegated[$item->delegated] ?></td>
      <td><?= $statuses[$item->status] ?></td>
      <td><?= empty($item->phones) ? '<img src="/icons/tick_6817.png" alt="">' : '' ?></td>
      <td><?= empty($item->emails) ? '<img src="/icons/tick_6817.png" alt="">' : '' ?></td>
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