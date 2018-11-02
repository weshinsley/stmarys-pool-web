<?php
  include "../status_file.php";

  $statuses = array(1 => $FS_AUTO, 2 => $FS_OPEN, 3 => $FS_CLOSED, 4 => $FS_SCHOOL); 
  $f_status = $FS_AUTO;
  if (isset($_POST['f_status'])) $f_status = $statuses[$_POST['f_status']];  
  $f_lanes = "4";
  if (isset($_POST['f_lanes'])) $f_lanes = $_POST['f_lanes'];
  $f_swimmers = "0";
  if (isset($_POST['f_swimmers'])) $f_swimmers = $_POST['f_swimmers'];
  $f_msgon = "0";
  if (isset($_POST['f_msgon'])) $f_msgon = $_POST['f_msgon'];
  $f_msg = base64_encode("");
  if (isset($_POST['f_msg'])) $f_msg = $_POST['f_msg'];
  $f_tt = base64_encode("");
  if (isset($_POST['f_tt'])) $f_tt = $_POST['f_tt'];
  $settings = $f_status.",".$f_lanes.",".$f_swimmers.",".$f_msgon.",".$f_msg.",".$f_tt;

  writeStatus("../", $settings);
  
  $result="";
  $result.= "document.fsettings.StAuto.checked = ".(($f_status == $FS_AUTO)?"true;":"false;")."\n";
  $result.= "document.fsettings.StFOpen.checked = ".(($f_status == $FS_OPEN)?"true;":"false;")."\n";
  $result.= "document.fsettings.StFClosed.checked = ".(($f_status == $FS_CLOSED)?"true;":"false;")."\n";
  $result.= "document.fsettings.StFSchool.checked = ".(($f_status == $FS_SCHOOL)?"true;":"false;")."\n";
  $result.= "document.fsettings.lanes.value = ".$f_lanes.";\n";
  $result.= "document.fsettings.swimmers.value = ".$f_swimmers.";\n";
  $result.= "document.fsettings.SpMsgOn.checked = ".(($f_msgon == "1")?"true;":"false;")."\n";
  $result.= "document.fsettings.SpMsg.value = decode64('".$f_msg."');\n";

  include "num_slider.php";
  $lane_string = do_num_slider($f_lanes,0,4,"up_lanes()","down_lanes()");
  $swimmers_string = do_num_slider($f_swimmers,0,100,"up_swimmers()","down_swimmers()");
  $result.= "$(\".update_lanes\").html(\"".$lane_string."\");";
  $result.= "$(\".update_swimmers\").html(\"".$swimmers_string."\");";   
  $result.= "$(\".update_tt\").html(\"<select name='tt' onchange='javascript:change_tt();'>";

  include "../timetable_file.php";
  $tts = readTimetable("../");
  
  foreach($tts->tt as $tt) {
    $opname = htmlspecialchars(base64_decode($tt['name']));
    $selected="";
    if ($tt['name']==$f_tt) $selected = "selected='selected' ";
    $result.="<option ".$selected." value='".$tt['name']."'>".$opname."</option>";
  }
  $result.="</select>\");";
  
  echo $result;
?>