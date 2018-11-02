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
     <tr><td colspan="2" style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff; background:#00558E">
        &nbsp;&nbsp;&nbsp;<a href="index.php">Current Status</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<u>Timetable</u>&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="about.php">About</a></td><td style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff;text-align:right; background:#00558E">
        <a href="manage/">Management</a>&nbsp;&nbsp;&nbsp;</span></td></tr>
        
        <tr><td style="background:url('images/bg.png')" colspan="3"><center>&nbsp;<br/>
      
    &nbsp;<br/>
 <?php
   include "timetable_draw.php";
   draw_tt($tt,1);
   draw_tt_key();
 ?>
    </center></td></tr></table>
  </center></body>
</html>