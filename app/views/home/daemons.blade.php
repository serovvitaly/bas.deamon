<fieldset style="margin-top: 20px;">
  <legend>Управление демонами</legend>
  
  <button class="btn btn-danger" onclick="startDaemon();">Запустить ДЕМОН</button>
  
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