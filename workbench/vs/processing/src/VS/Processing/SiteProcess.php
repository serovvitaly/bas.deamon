<?php


/**
 * 
 */
class SiteProcess
{
    public function fire($job, $mix)
    {
        if (!isset($mix['ufile']) OR !($mix['ufile'] instanceof UploadFile) OR !isset($mix['handle']) OR $mix['handle'] === FALSE) {
            $job->delete();
            return false;
        }
        
        $ufile  = $mix['ufile'];
        $handle = $mix['handle'];
        
        $job->delete();
        return;
        
        while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
            $site = new Site;
            $site->url = $data[0];
            $site->save();
        }
        
        fclose($handle);
    }
}
