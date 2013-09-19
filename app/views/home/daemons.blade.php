<fieldset style="margin-top: 20px;">
  <legend>Управление демонами</legend>
  
  <button class="btn btn-danger" onclick="startDaemon();">Запустить ДЕМОН</button>
  
  <table class="table" style="margin-top: 20px;">
    <tbody>
    <?
        if (count($mix) > 0) {
            foreach ($mix AS $item) {
    ?>
      <tr>
        <td><?= isset($item['PID']) ? $item['PID'] : '?' ?></td>
        <td><?= isset($item['WCHAN']) ? $item['WCHAN'] : '?' ?></td>
        <td><?= isset($item['TIME']) ? $item['TIME'] : '?' ?></td>
        <td><?= isset($item['CMD']) ? $item['CMD'] : '' ?></td>
      </tr>
    <?
            }
        }
    ?>
    </tbody>
  </table>
  
</fieldset>


<script>
function startDaemon(){
    post('/start-daemon', {}, function(data){
        if (data.success === true) {                            
            //                  
        }
    });
}
</script>