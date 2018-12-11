<?php

  // Helper function 

  function find_next_slot($tt, $start_time, $start_day) {
    $debug = false;
    $days = array(0 => "Sunday", 1 => "Monday", 2 => "Tuesday", 3 => "Wednesday", 
                  4 => "Thursday", 5 => "Friday", 6 => "Saturday");

    $start_time_mins = (intval($start_time/100)*60)+($start_time % 100);
    if ($debug) echo "Start_time = ".$start_time.", start_day=".$start_day.", stm = ".$start_time_mins."<br/>\n";


    $best = null;
    $smallest_diff = PHP_INT_MAX;

    foreach ($tt->slot as $slot) {
      if ($debug) echo "CONSIDER ".$slot['day'].", ".$slot['start']."<br/>";
      if (($slot['type'] == 'open') || (strpos('lanes open', $slot['type'])>=0)) {
        $slot_day = array_search($slot['day'], $days);
        $slot_time = intval($slot['start']);
        $slot_time = (intval($slot_time/100)*60)+($slot_time % 100);
        if ($debug) echo "slot_day=".$slot_day.", slot_Time=".$slot_time.", stm = ".$start_time_mins."<br/>";
        
        if (($slot_day == $start_day) && ($slot_time < $start_time_mins)) $slot_day += 7;
        if ($slot_day < $start_day) $slot_day += 7;

        if ($debug) echo "Adjust slot day?: ".$slot_day."   start day: ".$start_day."<br/>";

        // Calculate difference in minutes...

        // Whole days of difference...
        $diff_mins = max(0, (($slot_day - $start_day)-1) * 1440);
        if ($debug) echo "diff_mins 1 = ".$diff_mins."<br/>";
    
        // Add end of current day
        if ($slot_day > $start_day) $diff_mins += (1440 - $start_time_mins);
        if ($debug) echo "diff_mins 2 = ".$diff_mins." stm = ".$start_time_mins."<br/>";
        
        // Add beginning of "next" day
        $diff_mins += $slot_time;
        if ($debug) echo "Diff_mins 3 = ".$diff_mins." slot_time = ".$slot_time."<br/>\n";

        if ($diff_mins < $smallest_diff) {
          if ($debug) echo "SELECT<br/>\n";
          $smallest_diff = $diff_mins;
          $best = $slot;
        }
      }
    }

    $result_day = $best['day'];
    if (($best['day'] == $days[$start_day]) && ($best['start']>$start_time)) $result_day = "Today";

    return $result_day.", ".substr($best['start'],0,2).":".substr($best['start'],2,2);
  }
  
  // Load status
  
  include "status_file.php";
  $fstatus = readStatus("");  

  // Get date and time

  date_default_timezone_set("Europe/London");
  $ts = time();
  $nice_date = date("D jS M, H:i:s", $ts);


  // Work out status and next sessions...

  $nice_status = "";
  $end_next = 0;
  $actual_status = $fstatus[$FS_TYPE];
  if ($actual_status == $FS_OPEN) {
    $nice_status = "<span style='color:Green'>OPEN</span>";
  } else if ($actual_status == $FS_SCHOOL) {
    $nice_status = "<span style='color:Blue'>SCHOOL USE ONLY</span>";
  } else if ($actual_status == $FS_CLOSED) {
    $nice_status = "<span style='color:Red'>CLOSED</span>";

  // And if in auto-mode, work out status/end of session/next...

  } else if ($actual_status == $FS_AUTO) {
    
    // Which session are we in?
    
    $time_h = intval(date("h", $ts));
    if ((date("a", $ts) == "pm") && ($time_h<12)) $time_h += 12;
    $time_4dig = strval(($time_h * 100) + intval(date("i", $ts)));
   
    while (strlen($time_4dig)<4) $time_4dig = "0".$time_4dig;
    $date_dow = date("l", $ts);
    $date_dayno = date("w",$ts);

//    $time_4dig = "1030";
//    $date_dow = "Tuesday";
//    $date_dayno = 2;
//    $nice_date = "Tues 23rd October, 10:30:01";

    // Load timetable

    include "timetable_file.php";
    $tts = readTimetable("");
    
    foreach ($tts->tt as $tt) {
      if ($tt['name'] == $fstatus[$FS_TT_NAME_ENC]) {
        break;
      }
    }

    // Find which slot we're in - if we are in one...
    $found = 0;
     foreach ($tt->slot as $slot) {
     
      if (($slot['day'] == $date_dow) &&
          (intval($slot['start']) <= $time_4dig) &&
          (intval($slot['end']) > $time_4dig)) {
      
        if ($slot['type'] == $TT_OPEN) {
          $nice_status = "<span style='color:Green'>OPEN</span>";
          $actual_status = $FS_OPEN;
          $session_end = substr($slot['end'],0,2).":".substr($slot['end'],2,2);
        } else if ($slot['type'] == $TT_SCHOOL) {
          $nice_status = "<span style='color:Blue'>SCHOOL USE ONLY</span>";
          $actual_status = $FS_SCHOOL;
        }

        // Find start of next session

        $session_next = find_next_slot($tt, 1+intval($slot['start']), $date_dayno);
        $found = 1;
        break;
      }
    }
    if ($found==0) {
      $actual_status = $FS_CLOSED;
      $nice_status = "<span style='color:Red'>CLOSED</span>";
      $session_next = find_next_slot($tt, intval($time_4dig), $date_dayno);
    }
  }

  $lanes = intval($fstatus[$FS_LANES]);
  $swimmers = intval($fstatus[$FS_SWIMMERS]);
  $aptemps = $fstatus[$FS_PTEMP]."&#8451; / ".$fstatus[$FS_ATEMP]."&#8451;";

  $result = "$(\".update_time\").html(\"".$nice_date."\");\n";
  $result.= "$(\".update_status\").html(\"".$nice_status."\");\n";
  if (($actual_status == $FS_CLOSED) || ($actual_status == $FS_SCHOOL)) {
    $result.= "$(\".row_lanes\").hide();\n";
    $result.= "$(\".row_swimmers\").hide();\n";
  } else {
    $result.= "$(\".update_lanes\").html(\"".$lanes."\");\n";
    $result.= "$(\".update_swimmers\").html(\"".$swimmers."\");\n";
    $result.= "$(\".row_lanes\").show();\n";
    $result.= "$(\".row_swimmers\").show();\n";
  }
  if ($fstatus[$FS_TYPE] == $FS_AUTO) {
    $result.= "$(\".update_next\").html(\"".$session_next."\");\n";
    $result.= "$(\".row_nextsession\").show();\n";
    if (($actual_status == $FS_CLOSED) || ($actual_status == $FS_SCHOOL)) {
      $result.= "$(\".row_endsession\").hide();\n";
    } else {
      $result.= "$(\".update_end\").html(\"".$session_end."\");\n";
      $result.= "$(\".row_endsession\").show();\n";
    }
  
  } else {
    $result.= "$(\".row_endsession\").hide();\n";
    $result.= "$(\".row_nextsession\").hide();\n";
  }

  if ($actual_status != $FS_CLOSED) {
    $result.="$(\".row_temps\").show();\n";
    $result.= "$(\".update_temps\").html(\"".$aptemps."\");\n";
    
  } else {
    $result.="$(\".row_temps\").hide();\n";
  } 

  if ($fstatus[$FS_MSGON]=='0') {
    $result.="$(\".special_msg\").html(\"&nbsp;<br/>\");\n";
  } else {
    $text = htmlspecialchars($fstatus[$FS_MSG]);
    $result.="$(\".special_msg\").html(\"<br/><span style='color:red'>PLEASE NOTE: </span>".$text."<br/>&nbsp;<br/>\");\n";
  }

  echo $result;

?>
