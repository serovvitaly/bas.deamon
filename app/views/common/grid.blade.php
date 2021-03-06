<table id="main-grid" class="table table-condensed table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th>Домен</th>
      <th>Страницы</th>
      <th>Делигирован</th>
      <th style="width: 86px;">Статус</th>
      <th>Телефоны</th>
      <th>Email-ы</th>
      <th>Дата п. обр</th>
      <th>Возраст домена, дн.</th>
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
      <td><?= $item->meet_links ?></td>
      <td><?= $delegated[$item->delegated] ?></td>
      <td><?= $statuses[$item->status] ?></td>
      <td style="text-align: center;"><?= ($item->phones_count > 0) ? '<div class="popover" data-toggle="popover" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus." data-placement="right" src="/packages/icons/tick_6817.png" alt=""></div><img src="/packages/icons/tick_6817.png" alt="">' : '' ?></td>
      <td style="text-align: center;"><?= ($item->emails_count > 0) ? '<div class="popover" data-toggle="popover" data-content="Vivamus sagittis lacus vel augue laoreet rutrum faucibus." data-placement="right" src="/packages/icons/tick_6817.png" alt=""></div><img src="/packages/icons/tick_6817.png" alt="">' : '' ?></td>
      <td><?= $item->updated_at ?></td>
      <td><?= ceil( (time() - strtotime($item['domain_created'])) / (3600 * 24)) ?></td>
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

<script>
//$('.popover').popover();
</script>