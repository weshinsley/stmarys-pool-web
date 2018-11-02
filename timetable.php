<?php
  // Get status - which timetable?
  
  include "status_file.php";
  include "timetable_file.php";

  $fstatus = readStatus("");
  $tts = readTimetable("");
  
 // Get the right timetable xml

  foreach ($tts->tt as $tt) {
    if ($tt['name']==$fstatus[$FS_TT_NAME_ENC]) {
      break;
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <style>

<?php  
  readfile("styles.css");
  
?>
    </style>
    <script language="JavaScript">
    </script>
  </head>
  <body><center>
    <table cellpadding="0" cellspacing="2">
      <tr><td><img src="images/ic.png" alt="" width="165" height="63"/></td>
          <td><img src="images/sm.png" alt="" width="334" height="63"/></td>
          <td><img src="images/si.png" alt="" width="165" height="63"/></td>
      </tr>
      <tr><td style="background:url('images/bg.png')" colspan="3"><center>&nbsp;<br/>
    
    &nbsp;<br/>
 <?php
   include "timetable_draw.php";
   draw_tt($tt,1);
 ?>
 
    </center></td></tr></table>
  </center></body>
</html>