<?php
  include "../status_file.php";

  $f_status = $FS_AUTO;
  if (isset($_POST['f_status'])) $f_status = $_POST['f_status'];  
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
  $f_atemp = 25;  
  if (isset($_POST['f_atemp'])) $f_atemp = $_POST['f_atemp'];
  $f_ptemp = 26;  
  if (isset($_POST['f_ptemp'])) $f_ptemp = $_POST['f_ptemp'];

  
  $settings = $f_status.",".$f_lanes.",".$f_swimmers.",".$f_msgon.",".$f_msg.",".$f_tt.",".$f_ptemp.",".$f_atemp;
  
  writeStatus("../", $settings);
  
  $result="";
  
  $result.= "document.fsettings.lanes.value = ".$f_lanes.";\n";
  $result.= "document.fsettings.swimmers.value = ".$f_swimmers.";\n";
  $result.= "document.fsettings.SpMsgOn.checked = ".(($f_msgon == "1")?"true;":"false;")."\n";
  $result.= "document.fsettings.SpMsg.value = decode64('".$f_msg."');\n";

  include "num_slider.php";
  $lane_string = do_num_slider($f_lanes,0,4,"up_lanes()","down_lanes()");
  $swimmers_string = do_num_slider($f_swimmers,0,100,"up_swimmers()","down_swimmers()");
  $result.= "$(\".update_lanes\").html(\"".$lane_string."\");";
  $result.= "$(\".update_swimmers\").html(\"".$swimmers_string."\");";   
  $temps = $f_ptemp."&#8451; / ".$f_atemp."&#8451;";
  $result.= "$(\".update_temps\").html(\"".$temps."\");\n";
  
  $result.= "$(\".update_force_status\").html(\"<select onchange='javascript:change_status();' name='force_status'>";
  for ($i=0; $i<count($FS_ALL_STATUSES); $i++) {
    $selected="";
    if ($f_status == $FS_ALL_STATUSES[$i]) $selected = "selected='selected'"; 
    $result.="<option ".$selected." name='".$FS_ALL_STATUSES[$i]."' value='".$FS_ALL_STATUSES[$i]."'>".$FS_ALL_STATUS_NAMES[$i]."</option>";
  }
  $result.="</select>\");\n";
  

  include "../timetable_file.php";
  $result.= "$(\".update_tt\").html(\"<select name='tt' onchange='javascript:change_tt();'>";
  
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