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
        var status = document.fsettings.force_status.value;
        var lanes = parseInt(document.fsettings.lanes.value) + ((func==1)?1:0) - ((func==2)?1:0)
        var swimmers = parseInt(document.fsettings.swimmers.value) + ((func==3)?1:0) - ((func==4)?1:0);
        var msg_on = 0;
        if (document.fsettings.SpMsgOn.checked) msg_on = 1;
        
        var msg = document.fsettings.SpMsg.value;
        if (msg=='') msg=' ';
        msg = encode64(msg);
        var tt = document.fsettings.tt.value;
        var atemp = document.fsettings.atemp.value;
        var ptemp = document.fsettings.ptemp.value;
        $.post("update_settings.php",{f_status : status, f_lanes : lanes, f_swimmers : swimmers, f_msgon : msg_on, f_msg : msg, f_tt : tt, f_atemp : atemp, f_ptemp: ptemp}, function(result) {
          eval(result);
        });

      }

      function up_lanes() { update_status(1); }
      function down_lanes() { update_status(2); }
      function up_swimmers() { update_status(3); }
      function down_swimmers() { update_status(4); }
      function change_tt() { update_status(5); }
      function change_status() { update_status(5); }

      function edit_special_msg() {
        var text = prompt("Please enter special message", document.fsettings.SpMsg.value);
        if (text!=null) {
          document.fsettings.SpMsg.value = text;
          update_status(5);
        }
      }
      
      function air_temp() {
        var text = prompt("Please enter air temperature (Celcius)", document.fsettings.atemp.value);
        if (text!=null) {
          if (text.trim().length>0) {
            if (isNaN(Number(text))) {
              alert("Decimal number expected. (eg, 25.5)");
            } else {
              document.fsettings.atemp.value = text;
              update_status(5);
            }
          }
        }
      }

      function pool_temp() {
        var text = prompt("Please enter pool temperature (Celcius)", document.fsettings.ptemp.value);
        if (text!=null) {
          if (text.trim().length>0) {
            if (isNaN(Number(text))) {
              alert("Decimal number expected. (eg, 25.5)");
            } else {
              document.fsettings.ptemp.value = text;
              update_status(5);
            }
          }
        }
      }

    </script>
  </head>
  <body><center>
    <table cellpadding="0" cellspacing="2">
      <tr><td><img src="../images/ic.png" alt="" width="165" height="63"/></td>
          <td><img src="../images/sm.png" alt="" width="334" height="63"/></td>
          <td><img src="../images/si.png" alt="" width="165" height="63"/></td>
      </tr>
        <tr><td colspan="2" style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff; background:#00558E">
        &nbsp;&nbsp;&nbsp;<u>Basic Settings</u>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="timetable_edit.php">Edit Timetables</a>&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td><td style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff;text-align:right; background:#00558E">
        <a href="../index.php">Exit Management</a>&nbsp;&nbsp;&nbsp;</td></tr>
 
      <tr><td style="background:url('../images/bg.png')" colspan="3"><center>&nbsp;<br/>
        <form name="fsettings">       
          <input type="hidden" name="lanes" value="0"/>
          <input type="hidden" name="swimmers" value="0"/>
          <input type="hidden" name="ptemp" value="25"/>
          <input type="hidden" name="atemp" value="25"/>
            
        <table class="info" cellpadding="10" cellspacing="4">
          <tr class="row_status"><td class="tinfo">Status:</td><td class="tdata">
            <div class="update_force_status">
            <select onchange="javascript:change_status();" name="force_status">
            <?php
              for ($i=0; $i<count($FS_ALL_STATUSES); $i++) {
                $selected="";
                if ($fstatus[$FS_TYPE] == $FS_ALL_STATUSES[$i]) $selected = "selected='selected'"; 
                echo "               <option ".$selected." name='".$FS_ALL_STATUSES[$i]."' value='".$FS_ALL_STATUSES[$i]."'>".$FS_ALL_STATUS_NAMES[$i]."</option>\n";
              }
            ?>
              
            </select></div>
          </td></tr>
          <tr class="row_tt"><td class="tinfo">Current timetable:</td><td class="bigdata">
            <div class="update_tt">
              <select name="tt" onchange="javascript:change_tt();">
              <?php
                include "../timetable_file.php";
                $tts = readTimeTable("../");
                foreach($tts->tt as $tt) {
                  $opname = htmlspecialchars(base64_decode($tt['name']));
                  $selected="";
                  if ($tt['name'] == $fstatus[$FS_TT_NAME_ENC]) $selected= "selected='selected'";
                  echo "<option ".$selected." value='".$tt['name']."'>".$opname."</option>";
                } 
              ?>
              </select>
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
          <tr class="row_temps"><td class="tinfo">Pool/Air Temperature:</td><td class="tdata">
            <div style="display:inline-block" class="update_temps">
            </div>&nbsp;&nbsp;<input type="button" value="Pool" onclick="javascript:pool_temp();"/>
            &nbsp;&nbsp;<input type="button" value="Air" onclick="javascript:air_temp();"/>
          </td></tr>

          <tr class="row_message"><td class="tinfo">Special Message:</td><td class="tdata">
            <input type="checkbox" name="SpMsgOn" onclick="javascript:update_status(5);"/>&nbsp;<input type="text" name="SpMsg" onkeydown="return (event.keyCode!=13);" readonly style="width:180px"/>
            <input type="button" value="Edit" name="Edit" onclick="javascript:edit_special_msg();"/>
          </td></tr>
              
        </table>&nbsp;</form><br/></center>
      </td></tr>
    </table>
  <script language="JavaScript">
    document.fsettings.lanes.value = <?=$fstatus[$FS_LANES] ?>;
    document.fsettings.swimmers.value = <?=$fstatus[$FS_SWIMMERS] ?>;
    document.fsettings.SpMsgOn.checked =<?php echo ($fstatus[$FS_MSGON] == "1")?"true":"false"; ?>;
    document.fsettings.SpMsg.value = "<?php echo $fstatus[$FS_MSG]; ?>";
    document.fsettings.atemp.value = "<?php echo $fstatus[$FS_ATEMP]; ?>";
    document.fsettings.ptemp.value = "<?php echo $fstatus[$FS_PTEMP]; ?>";
    $(".update_temps").html("<?php echo htmlspecialchars($fstatus[$FS_PTEMP]).'&#8451; / '.htmlspecialchars($fstatus[$FS_ATEMP]).'&#8451;'; ?>");
  
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