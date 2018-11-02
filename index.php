<?php
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
<?php  
  readfile("styles.css");  
?>
    </style>
    <script src="scripts/jquery-1.12.4.min.js"></script>
    <script src="scripts/base64.js"></script>
    <script language="JavaScript">

      function update_status() {
        $.post("update_all.php",{}, function(result) {
          eval(result);
        });

      }
    </script>
  </head>
  <body><center>
    <table cellpadding="0" cellspacing="2">
      <tr><td><img src="images/ic.png" alt="" width="165" height="63"/></td>
          <td><img src="images/sm.png" alt="" width="334" height="63"/></td>
          <td><img src="images/si.png" alt="" width="165" height="63"/></td>
      </tr>
      <tr><td style="background:url('images/bg.png')" colspan="3"><center><div class="special_msg">&nbsp;<br/></div>

    <table class="info" cellpadding="10" cellspacing="4">
      <tr class="row_time"><td class="tinfo">Status at:</td><td class="tdata"><div class="update_time">&nbsp;</div></td></tr>
      <tr class="row_status"><td class="tinfo">Current Status:</td><td class="tdata"><div class="update_status">&nbsp;</div></td></tr>
      <tr class="row_lanes"><td class="tinfo">Lanes Available:</td><td class="tdata"><div class="update_lanes">&nbsp;</div></td></tr>
      <tr class="row_swimmers"><td class="tinfo">Current Swimmers:</td><td class="tdata"><div class="update_swimmers">&nbsp;</div></td></tr>
      <tr class="row_endsession"><td class="tinfo">End of Session:</td><td class="tdata"><div class="update_end">&nbsp;</td></div></tr>
      <tr class="row_nextsession"><td class="tinfo">Next Open Session:</td><td class="tdata"><div class="update_next">&nbsp;</td></div></tr>
    </table>&nbsp;<br/></center></td></tr></table>

    &nbsp;<br/>

  <script language="JavaScript">
    update_status();
    setInterval(update_status, 30000);
  </script>
  </center></body> 
</html>