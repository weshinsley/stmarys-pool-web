<?php
  
  include "../timetable_file.php";
  $tts = readTimeTable("../");
  
  include "../status_file.php";
  $fstatus = readStatus("../");
  
  $set_tt = $fstatus[$FS_TT_NAME_ENC];
  if (isset($_POST['set_tt'])) {
    if (strlen(trim($_POST['set_tt']))>0) {
      $set_tt = $_POST['set_tt'];
    }
  }
    
  $TT_TIMES = array("0700","0730","0800","0830","0900","0930","1000","1030","1100","1130","1200","1230","1300","1330","1400",
                 "1430","1500","1530","1600","1630","1700","1730","1800","1830","1900");
  

  // XML -> some horrible structures...

  $tt_days = array();
  $tt_types = array();
  $tt_starts = array();
  $tt_ends = array();
  $slots=0;
  $days = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
  $tt=0;
  foreach ($tts->tt as $tt) {
    if ($tt['name'] == $set_tt)
      break;
  }

  foreach ($tt->slot as $slot) {
    $tt_days[$slots] = array_search($slot['day'],$days);
    $tt_types[$slots] = $slot['type'];
    $tt_starts[$slots] = $slot['start'];
    $tt_ends[$slots] = $slot['end'];
    $slots++;
  }
  
  // And sort them.

  for ($i=0; $i<$slots-1; $i++) {
    $earliest=$i;
    for ($j=$i+1; $j<$slots; $j++) {
      if (($tt_days[$j]<$tt_days[$earliest]) ||
         (($tt_days[$j]==$tt_days[$earliest]) && (intval($tt_starts[$j])<intval($tt_starts[$earliest])))) {
         $earliest=$j;
      }
    }
    if ($earliest!=$i) {
      if ($tt_days[$i]!=$tt_days[$earliest]) {
        $d = $tt_days[$i];
        $tt_days[$i]=$tt_days[$earliest];
        $tt_days[$earliest]=$d;
      }
      if ($tt_types[$i]!=$tt_types[$earliest]) {
        $d = $tt_types[$i];
        $tt_types[$i]=$tt_types[$earliest];
        $tt_types[$earliest]=$d;
      }
      if ($tt_starts[$i]!=$tt_starts[$earliest]) {
        $d = $tt_starts[$i];
        $tt_starts[$i]=$tt_starts[$earliest];
        $tt_starts[$earliest]=$d;
      }
      if ($tt_ends[$i]!=$tt_ends[$earliest]) {
        $d = $tt_ends[$i];
        $tt_ends[$i]=$tt_ends[$earliest];
        $tt_ends[$earliest]=$d;
      }
    }
  }
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
 
      var times = ["0700","0730","0800","0830","0900","0930","1000","1030","1100","1130","1200","1230","1300","1330","1400",
                     "1430","1500","1530","1600","1630","1700","1730","1800","1830","1900"];
      var unsaved = false;
      <?php
        
        // Now arrange the data into a hideous javascript object.

        echo "var data = [";
        for ($i=0; $i<$slots; $i++) {
          echo "'".$tt_days[$i]."','".$tt_types[$i]."','".$tt_starts[$i]."','".$tt_ends[$i]."'";
          if ($i<$slots-1) echo ",";
        }
        echo "];\n";
      ?>

      function set_unsaved() {
        unsaved = true;
        $(".img_unsaved").html("<a href='javascript:save_tt()'><img style='vertical-align:bottom' src='../images/tick.gif' alt='' title='Save Changes'/></a>");
      }

      function save_changes() {
        unsaved = false;
        $(".img_unsaved").html("<img style='vertical-align:bottom' src='../images/gtick.png' alt=''/>");
      }

      function rename_tt() {
        var orig = document.getElementById("choose_tt").value;
        var n = prompt("New name for the new timetable?");
        if (n!=null) {
          n = n.trim();
          if (n.length>0) {
            $.post("timetable_funcs.php", {tto : orig, ttn : encode64(n), dat : data, func : <?=$TT_F_RENAME ?>}, function(result) {
              eval(result);
            });
          }
        }
      }

      function add_tt() {
        if (unsaved) {
          if (!confirm("Unsaved changes to this timetable will be lost! Ok to continue?")) {
            return;
          }
        }
        var n = prompt("This creates a duplicate of the timetable on screen, and opens it. What name for the new timetable?");
        if (n!=null) {
          n = n.trim();
          if (n.length>0) {
            $.post("timetable_funcs.php", {ttn : encode64(n), dat : data, func : <?=$TT_F_DUP ?>}, function(result) {
              eval(result);
            });
          }
        }
      }
      
      function delete_tt() {
        var n = document.getElementById("choose_tt").value;
        if (confirm("Confirm - delete this timetable?")) {
          $.post("timetable_funcs.php", {ttn : n, dat : data, func : <?=$TT_F_DELETE ?>}, function(result) {
            eval(result);
          });
        }
      }
       
      
      function save_tt() {
        var n = document.getElementById("choose_tt").value;
        $.post("timetable_funcs.php", {ttn : n, dat : data, func : <?=$TT_F_SAVE ?>}, function(result) {
          eval(result);
        });
      }

      function add_slot() {
        new_t1 = parseInt(document.fslot.time1.value);
        new_t2 = parseInt(document.fslot.time2.value);
        new_day = parseInt(document.fslot.day.value);
        // time1 and time2 0..23 and 1..24

        if (new_t1 == new_t2) {
          alert("Error - that slot is zero minutes long");
          return;
        }
        
        if (new_t2 < new_t1) {
          alert("Error - end time is before start time");
          return;
        }

        // Check for overlap
        for (i=0; i<data.length; i+=4) {
          if (data[i]==new_day) {
            t1_index = times.indexOf(data[i+2]);
            t2_index = times.indexOf(data[i+3]);
            if (((new_t1 >= t1_index) && (new_t1 < t2_index)) ||
                ((new_t2 > t1_index) && (new_t2 <= t2_index)) ||
                ((new_t1 <= t1_index) && (new_t2 >= t2_index))) {
              alert("Error - this overlaps with an existing slot");
              return;
            }
          }
        }

        // If we survived, the slot is ok.

        var found=0;
        for (i=0; i<data.length; i+=4) {
          if ((data[i] > new_day) ||
             ((data[i] == new_day) && (times.indexOf(data[i+2])>=new_t1))) {
            found=1;
            data.splice(i,0,document.fslot.day.value, document.fslot.stype.value, times[new_t1], times[new_t2]);
            break;
          }
        }
        if (found==0) {
          data = data.concat([document.fslot.day.value, document.fslot.stype.value, times[new_t1], times[new_t2]]);
        }
        draw_table();
        draw_timetable();
        set_unsaved();
      }

      function del_slot(index) {
        data.splice(index*4,4);
        draw_table();
        draw_timetable();
        set_unsaved();
      }
      
      var last_tt;

      function click_tt() {
        last_tt = document.getElementById("choose_tt").value;
      }

      function change_tt() {
        if (unsaved) {
          if (!confirm("Unsaved changes will be lost! Ok to continue?")) {
            document.getElementById("choose_tt").value = last_tt;
            return;
          }
        }
        last_tt = document.getElementById("choose_tt").value;
        document.freload.set_tt.value=last_tt;
        document.freload.submit();
      }

      function draw_table() {
        var days = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
        var s = "<table class='info'>";
        var slots = data.length/4;
        for (i=0; i<slots; i++) {
          s = s + "<tr class='ttedit'>";
          s = s + "<td>" + days[data[(i*4)]] +"</td>";
          s = s + "<td>" + data[(i*4)+1] + "</td>";
          var t = data[(i*4)+2];
          s = s + "<td>" + t.substring(0,2) +":" + t.substring(2,4) + "</td>";
          t = data[(i*4)+3];
          s = s + "<td>" + t.substring(0,2) +":" + t.substring(2,4) + "</td>";
          s = s + "<td><a href='javascript:del_slot("+i+");'><img alt='' src='../images/del.png' style='width:24px;height:24px'/></a></td></tr>";
          if ((i==slots-1) || (data[(i*4)] != data[(i*4)+4])) {
            s = s + "<tr><td colspan='5'><hr/></td></tr>";
          }
        }
        s = s + "</table>";
        $(".tt_table").html(s);
      }

      function draw_timetable() {
        var start_time = 7;
        var end_time = 19;
        var steps = (end_time - start_time)/0.5;
        var no_days = 7;
        var days = ["M","T","W","T","F","S","S"];
        var s = "<table style='border-spacing:0px; border-collapse:collapse;'><tr>\n";
        var colwid = 20;
        var rowhei = 22;
        var types = ["","open","school","3 lanes open", "2 lanes open","1 lane open"];
        var cols = ["#909090","#90d090","#9090d0","#ff0000","#ff0000","#ff0000"];

        // Build a matrix from the grubby javascript "data" structure...

        var matrix = [[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
                      [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]];
       
    
        for (i=0; i<data.length; i+=4) {
          var day = parseInt(data[i]);
          var time1 = times.indexOf(data[i+2]);
          var time2 = times.indexOf(data[i+3]);
          var ttype = types.indexOf(data[i+1]);
          for (t=time1; t<time2; t++) {
            matrix[day][t]=ttype;
          }
        }
      
        // The times before the tick marks

        s = s + "<td valign='top' style='padding:0px; text-align:right;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:30px'>";
        s = s + "<table style='border-spacing:0px; border-collapse:collapse;'>\n";
        s = s + "    <tr height='10px'><td></td></tr>\n";
        
        for (h=7; h<=19; h++) {
          var hs = ((h<10)?"0"+h:h)+":00&nbsp;";
          s = s + "    <tr><td valign='top' style='padding:0px; width:10px;height:"+rowhei+"px'>"+hs+"</td></tr>\n";
        }
        s = s + "  </table></td>\n";
 
        // The tick marks before the left-most vertical line
 
        s = s + "<td valign='top' style='padding:0px; text-align:center;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:10px'>&nbsp;<br/>\n";
        s = s + "  <table style='border-spacing:0px; border-collapse:collapse;'>\n";
        s = s + "    <tr><td valign='top' style='padding:0px; width:10px;height:1px;background-color:#404040'></td></tr>\n";

        var t1 = 0;
        while (t1 < steps) {
          s = s + "    <tr><td style='padding:0px; width:10px;height:"+(rowhei-1)+"px'></td></tr>\n";
          s = s + "    <tr><td style='padding:0px; width:10px;height:1px;background-color:#404040'></td></tr>\n";
          t1 = t1 + 2
        }
        s = s + "  </table></td>\n";
    
        // Vertical line before first day column
        s = s + "<td style='padding:0px; background-color:#404040; width:1px'></td>\n";
 
        // For each day, do the day-column, followed by vertical line.
 
        for (d = 0; d <no_days; d++) {
          s = s + "<td valign='top' style='padding:0px; text-align:center;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:"+colwid+"px'>"+days[d]+"<br/>\n";
          s = s + "  <table style='border-spacing:0px; border-collapse:collapse;'>\n";
          s = s + "    <tr><td style='padding:0px; width:"+colwid+"px;height:1px;background-color:#404040'></td></tr>\n";
        
          t1 = 0;
          t2 = 0;
          while (t1 < steps) {
            var current_type = matrix[d][t1];
            while ((t2 < steps-1) && (matrix[d][t2+1]==current_type)) t2++;
            height = (1+(t2-t1))*((rowhei/2)-1);
            height = height + (t2-t1);
            if (current_type < 3) {
              
              s = s + "    <tr><td style='padding:0px; width:"+colwid+"px;height:"+height+"px;background-color:"+cols[current_type]+"'></td></tr>\n";
            
            } else {
              var cw1 = 0;
              var cw2 = 0;
              if (current_type == 3) cw1 = colwid / 4; 
              else if (current_type == 4) cw1 = colwid / 2; 
              else if (current_type == 5) cw1 = 3 * (colwid / 4);
              var cw2 = colwid - cw1;
          
              s = s +  "    <tr><td style='padding:0px; width:"+colwid+"px;height:"+height+"px;'><table cellpadding=\"0\" cellspacing=\"0\"><tr>";
              s = s +  "      <td style='padding:0px; width:"+cw1+"px;height:"+height+"px;background-color:"+cols[current_type]+"'></td>\n";
              s = s +  "      <td style='padding:0px; width:"+cw2+"px;height:"+height+"px;background-color:"+cols[1]+"'></td>\n";
              s = s +  "    </tr></table></td></tr>\n";
            }
              s = s + "    <tr><td style='padding:0px; width:"+colwid+"px;height:1px;background-color:#404040'></td></tr>\n";

            
            
            t1 = t2 + 1;
            t2 = t1;
          }
          s = s + "  </table></td>\n";
          s = s + "<td style='padding:0px; background-color:#404040; width:1px'></td>\n"; 
        }
        s = s + "</tr><tr style='height:10px'><td></td></tr></table>\n";
        $(".tt_preview").html(s);
      }

  </script>
  </head>
  <body><center>
    <form name="freload" method="post" action="timetable_edit.php">
      <input type="hidden" name="set_tt" value=""/>
    </form>
    <table cellpadding="0" cellspacing="2">
      <tr><td><img src="../images/ic.png" alt="" width="165" height="63"/></td>
          <td><img src="../images/sm.png" alt="" width="334" height="63"/></td>
          <td><img src="../images/si.png" alt="" width="165" height="63"/></td>
      </tr>
          <tr><td colspan="2" style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff; background:#00558E">
        &nbsp;&nbsp;&nbsp;<a href="index.php">Basic Settings</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<u>Edit Timetables</u>&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td><td style="padding:2px; font-family:Calibri,Arial;font-size:14px;color:#ffffff;text-align:right; background:#00558E">
        <a href="../index.php">Exit Management</a>&nbsp;&nbsp;&nbsp;</span></td></tr>
      <tr><td style="background:url('../images/bg.png')" colspan="3"><center>
        <table class="info" cellpadding="10" cellspacing="4">
          <tr><td class="tinfo" style="width:200px">Timetable to edit:</td><td style="width:400px" class="tdata">
            <select id="choose_tt" onChange="javascript:change_tt();" onClick="javascript:click_tt();" style="width:200px">
            <?php
              foreach($tts->tt as $ttmenu) {
                $tt_decode = base64_decode($ttmenu['name']);
                $opname = htmlspecialchars($tt_decode);
                $selected="";
                echo "<option value='".$ttmenu['name']."'>".$opname."</option>\n";
              }
            ?>
          </select>&nbsp;<a href="javascript:rename_tt()"><img style="vertical-align:bottom" src="../images/rename.png" alt="" title="Rename Timetable" /></a>
                         <a href="javascript:add_tt()"><img style="vertical-align:bottom" src="../images/add.png" alt="" title="New Timetable"/></a>
                         <a href="javascript:delete_tt()"><img style="vertical-align:bottom" src="../images/bigdel.png" alt="" title="Delete Timetable"/></a>&nbsp;&nbsp;&nbsp;&nbsp;
                         <div style="display: inline-block;" class="img_unsaved"><img style="vertical-align:bottom" src="../images/gtick.png" alt=""/></div>
       </td></tr>
       <tr><td style="font-family: Calibri,Arial; font-size: 15pt;" colspan="3">
         <table><tr>
           <td style="width:334px"><center>
             <div class="tt_preview">
             </div></center>
           </td>
           <td style="width:334x"><center>
             <div style="overflow:scroll; overflow-x:hidden; min-width:300px;height:300px;" class="tt_table"> 
             </div>&nbsp;<br/></center>
           </td></tr>
      <tr><td colspan="3"><center>
        <form name="fslot">
        <select name="day" style="width:120px">
        <?php
          for ($i=0; $i<7; $i++) {
            echo "<option value='".$i."'>".$days[$i]."</option>\n";
          }
        ?>
        </select>
        <select name="stype" style="width:80px">
          <option value="open">Open</option>
          <option value="school">School</option>
          <option value="3 lanes open">3 Lanes Open</option>
          <option value="2 lanes open">2 Lanes Open</option>
          <option value="1 lanes open">1 Lanes Open</option>
        </select>&nbsp;from&nbsp;
        <select name="time1" style="width:80px">
        <?php
           for ($i=0; $i<count($TT_TIMES)-1; $i++) {
            echo "<option value='".$i."'>".$TT_TIMES[$i]."</option>\n";
          }
        ?>
        </select>&nbsp;to&nbsp;
        <select name="time2" style="width:80px">
        <?php
          for ($i=1; $i<count($TT_TIMES); $i++) {
            echo "<option value='".$i."'>".$TT_TIMES[$i]."</option>\n";
          }
        ?>
        </select>&nbsp;<a href="javascript:add_slot()"><img style="vertical-align:bottom" src="../images/add.png" alt="" title="Add Slot"/></a>
        </form>
      </center></td></tr>
    </table>
  <script language="JavaScript">
    document.getElementById("choose_tt").value = '<?=$set_tt ?>';
    last_tt = document.getElementById("choose_tt").value;
    draw_table();
    draw_timetable();
  </script>
  </center></body>
</html>