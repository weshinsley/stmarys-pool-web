<?php
  include "../status_file.php";
  $fstatus = readStatus("../");
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <style>
      <?php readfile("styles.css"); ?>
    </style>
    <script src="../scripts/jquery-1.12.4.min.js"></script>
    <script src="../scripts/base64.js"></script>    
    <script language="JavaScript">

      function update_status(func) {
        var status=0;
        if (document.fsettings.StAuto.checked) status = 1;
        else if (document.fsettings.StFOpen.checked) status = 2;
        else if (document.fsettings.StFClosed.checked) status = 3;
        else if (document.fsettings.StFSchool.checked) status = 4;
        var lanes = parseInt(document.fsettings.lanes.value) + ((func==1)?1:0) - ((func==2)?1:0)
        var swimmers = parseInt(document.fsettings.swimmers.value) + ((func==3)?1:0) - ((func==4)?1:0);
        var msg_on = 0;
        if (document.fsettings.SpMsgOn.checked) msg_on = 1;
        
        var msg = document.fsettings.SpMsg.value;
        if (msg=='') msg=' ';
        msg = encode64(msg);
        var tt = document.fsettings.tt.value;
        $.post("update_settings.php",{f_status : status, f_lanes : lanes, f_swimmers : swimmers, f_msgon : msg_on, f_msg : msg, f_tt : tt}, function(result) {
          eval(result);
        });

      }

      function up_lanes() { update_status(1); }
      function down_lanes() { update_status(2); }
      function up_swimmers() { update_status(3); }
      function down_swimmers() { update_status(4); }
      function change_tt() { update_status(5); }

      function edit_special_msg() {
        var text = prompt("Please enter special message", document.fsettings.SpMsg.value);
        if (text!=null) {
          document.fsettings.SpMsg.value = text;
          update_status(5);
        }
      }


      function tick(x) {
        var lanes = document.fsettings.lanes.value;
        var swimmers = document.fsettings.swimmers.value;
        var msg_on = 0;
        if (document.fsettings.SpMsgOn.checked) msg_on = 1;
        var msg = encode64(document.fsettings.SpMsg.value);
        $.post("update_settings.php",{f_status : x, f_lanes : lanes, f_swimmers : swimmers, f_msgon : msg_on, f_msg : msg}, function(result) {
          eval(result);
        });
      }
        
    </script>
  </head>
  <body><center>
    <table cellpadding="0" cellspacing="2">
      <tr><td><img src="../images/ic.png" alt="" width="165" height="63"/></td>
          <td><img src="../images/sm.png" alt="" width="334" height="63"/></td>
          <td><img src="../images/si.png" alt="" width="165" height="63"/></td>
      </tr>
      <tr><td style="background:url('../images/bg.png')" colspan="3"><center>&nbsp;<br/>
        <form name="fsettings">       
        <table class="info" cellpadding="10" cellspacing="4">
          <tr class="row_status"><td class="tinfo">Status:</td><td class="tdata">
            <input type="hidden" name="lanes" value="0"/>
            <input type="hidden" name="swimmers" value="0"/>
            <table>
              <tr><td><input onclick="javascript:tick(1);" type="checkbox" name="StAuto"/>&nbsp;Automatic (uses timetable)<br/></td></tr>
              <tr><td><input onclick="javascript:tick(2);" type="checkbox" name="StFOpen"/>&nbsp;Force: Open<br/></td></tr>
              <tr><td><input onclick="javascript:tick(3);" type="checkbox" name="StFClosed"/>&nbsp;Force: Closed<br/></td></tr>
              <tr><td><input onclick="javascript:tick(4);" type="checkbox" name="StFSchool"/>&nbsp;Force: School use<br/></td></tr>
            </table>
          </td></tr>
          <tr class="row_tt"><td class="tinfo">Current timetable:</td><td class="bigdata">
            <div class="update_tt"><?php

              include "../timetable_file.php";
              $tts = readTimeTable("../");
              echo "<select name=\"tt\" onchange=\"javascript:change_tt();\">\n";
              foreach($tts->tt as $tt) {
                $opname = htmlspecialchars(base64_decode($tt['name']));
                echo "<option value='".$tt['name']."'>".$opname."</option>";
              } 
              echo "</select>";
              ?>
            </div>
          </td></tr>
          <tr class="row_lanes"><td class="tinfo">Free Lanes:</td><td class="bigdata">
            <div class="update_lanes">
            </div>
          </td></tr>
          <tr class="row_swimmers"><td class="tinfo">Total Swimmers:</td><td class="bigdata">
            <div class="update_swimmers">
            </div>
          </td></tr>
          <tr class="row_message"><td class="tinfo">Special Message:</td><td class="tdata">
            <input type="checkbox" name="SpMsgOn" onclick="javascript:update_status(5);"/>&nbsp;<input type="text" name="SpMsg" onkeydown="return (event.keyCode!=13);" readonly style="width:180px"/>
            <input type="button" value="Edit" name="Edit" onclick="javascript:edit_special_msg();"/>
          </td></tr>
              
        </table>&nbsp;</form><br/></center>
      </td></tr>
    </table>
  <script language="JavaScript">
    document.fsettings.StAuto.checked =<?php echo ($fstatus[$FS_TYPE] == $FS_AUTO)?"true":"false"; ?>;
    document.fsettings.StFOpen.checked =<?php echo ($fstatus[$FS_TYPE] == $FS_OPEN)?"true":"false"; ?>;
    document.fsettings.StFClosed.checked =<?php echo ($fstatus[$FS_TYPE] == $FS_CLOSED)?"true":"false"; ?>;
    document.fsettings.StFSchool.checked =<?php echo ($fstatus[$FS_TYPE] == $FS_SCHOOL)?"true":"false"; ?>;
    document.fsettings.lanes.value = <?=$fstatus[$FS_LANES] ?>;
    document.fsettings.swimmers.value = <?=$fstatus[$FS_SWIMMERS] ?>;
    document.fsettings.SpMsgOn.checked =<?php echo ($fstatus[$FS_MSGON] == "1")?"true":"false"; ?>;
    document.fsettings.SpMsg.value = "<?php echo $fstatus[$FS_MSG]; ?>";
    <?php
      include "num_slider.php";
      $lane_string = do_num_slider($fstatus[$FS_LANES],0,4,"up_lanes()","down_lanes()");
      $swimmers_string = do_num_slider($fstatus[$FS_SWIMMERS],0,100,"up_swimmers()","down_swimmers()");
    ?>

    $(".update_lanes").html("<?=$lane_string ?>");
    $(".update_swimmers").html("<?=$swimmers_string ?>");   
  </script>
  </center></body>
</html>