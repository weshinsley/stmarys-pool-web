<?php
  
  if ((isset($_POST['ttn'])) && (isset($_POST['dat'])) && (isset($_POST['func']))) {
    $func = $_POST['func'];
    $ttn = $_POST['ttn'];
    $dat = $_POST['dat'];

    include "../timetable_file.php";
    $tts = readTimetable("../");
    $ttn_exists = false;
    $count_tts = 0;
    $target_index = 0;
    foreach ($tts->tt as $tt) {
      if ($tt['name'] == $ttn) {
        $ttn_exists = true;
        $target_index = $count_tts;
      }
      $count_tts++;
    }
    
    // Duplicate timetable

    if ($func==$TT_F_DUP) {
      if ($ttn_exists) {
        echo "alert('A timetable with that name already exists');";
      } else {
        add_tt("../", $tts, $ttn, $dat);
        $res = "document.freload.set_tt.value='".$ttn."';\n";
        $res.= "document.freload.submit();\n";
        echo $res;
      }
    }

    // Rename timetable

    else if ($func==$TT_F_RENAME) {
      if ($ttn_exists) {
        echo "alert('A timetable with that name already exists');";
      } else {
        $tto = $_POST['tto'];
        rename_tt("../", $tts, $tto, $ttn);
        $res = "document.freload.set_tt.value='".$ttn."';\n";
        $res.="document.freload.submit();\n";
        echo $res;
      }
    }

    // Delete timetable

    else if ($func==$TT_F_DELETE) {
      if ($count_tts==1) {
        echo "alert('Cannot delete the last remaining timetable.');";
      } else {
        include "../status_file.php";
        $fstatus = readStatus("../");
        if ($fstatus[$FS_TT_NAME_ENC] == $ttn) {
          echo "alert('Cannot delete the active timetable.');";
        } else {
          delete_tt("../", $tts, $ttn);
         
          if ($target_index>$count_tts-2) $target_index=$count_tts-2;
          
          $i=0;
          $tto="";
          foreach ($tts->tt as $tt) {
            if (!($tt['name']==$ttn)) {
              if ($target_index==$i) {
                $tto=$tt['name'];
                break;
              }
              $i++;
            }
          }
          
          $res= "document.freload.set_tt.value='".$tto."';\n";
          $res.= "document.freload.submit();\n";
          echo $res;
        }
      }
    }

    // Save data

    else if ($func==$TT_F_SAVE) {
      save_tt("../", $tts, $ttn, $dat);
      $res = "save_changes();\n";
      echo $res;
    }
  }
?>