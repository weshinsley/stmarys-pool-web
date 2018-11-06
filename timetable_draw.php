<?php
  $TT_TIMES = array("0700","0730","0800","0830","0900","0930","1000","1030","1100","1130","1200","1230","1300","1330","1400",
                 "1430","1500","1530","1600","1630","1700","1730","1800","1830","1900");
  
  function draw_tt($tt, $type = 1) {
    global $TT_OPEN, $TT_SCHOOL,$TT_TIMES, $TT_ALL_TYPES,$TT_OPEN_3,$TT_OPEN_2,$TT_OPEN_1;
    $start_time = 7;
    $end_time = 19;
    $steps = ($end_time-$start_time)/0.5;
    $no_days = 7;

    $matrix = array();
    for ($i=0; $i<$no_days; $i++) {
      $day_array = array();
      for ($j=0; $j<$steps; $j++) {
        $day_array[$j] = 0;
      }
      $matrix[$i] = $day_array;
    }


    // Create matrix of days/times
  
    $days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $sdays = array("MON","TUE","WED","THU","FRI","SAT","SUN");
    $tdays = array("M","T","W","T","F","S","S");
    $cols = array("#909090","#90d090","#9090d0","#ff0000","#ff0000","#ff0000");
    $daylabels = $sdays;
    $colwid = 50;
    $rowhei = 26;

    if ($type==2) {
      $daylabels = $tdays;
      $colwid = 20;
      $rowhei = 22;
    }
    foreach ($tt->slot as $slot) { 
      $day_index = array_search($slot['day'], $days);
      $time1_index = array_search($slot['start'], $TT_TIMES);
      $time2_index = array_search($slot['end'], $TT_TIMES);
      $type_index = array_search($slot['type'], $TT_ALL_TYPES);
      for ($i = $time1_index; $i < $time2_index; $i++) {
        $matrix[$day_index][$i]=$type_index;
      }
    }
 
    echo "<table style='border-spacing:0px; border-collapse:collapse;'><tr>\n";
 
    // The times before the tick marks

    echo "<td valign='top' style='padding:0px; text-align:right;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:30px'>";
    echo "  <table style='border-spacing:0px; border-collapse:collapse;'>\n";
    echo "    <tr height='10px'><td></td></tr>\n";
    for ($h=7; $h<=19; $h++) {
      $hs = (($h<10)?"0".$h:$h).":00&nbsp;";
      echo "    <tr><td valign='top' style='padding:0px; width:10px;height:".$rowhei."px'>".$hs."</td></tr>\n";
    }
    echo "  </table></td>\n";
 
    // The tick marks before the left-most vertical line
 
    echo "<td valign='top' style='padding:0px; text-align:center;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:10px'>&nbsp;<br/>\n";
    echo "  <table style='border-spacing:0px; border-collapse:collapse;'>\n";
    echo "    <tr><td valign='top' style='padding:0px; width:10px;height:1px;background-color:#404040'></td></tr>\n";

    $t1 = 0;
    while ($t1<$steps) {
      echo "    <tr><td style='padding:0px; width:10px;height:".($rowhei-1)."px'></td></tr>\n";
      echo "    <tr><td style='padding:0px; width:10px;height:1px;background-color:#404040'></td></tr>\n";
      $t1=$t1+2;
    }
    echo "  </table></td>\n";
    
    // Vertical line before first day column
    echo "<td style='padding:0px; background-color:#404040; width:1px'></td>\n";
 
    // For each day, do the day-column, followed by vertical line.
 
    for ($d = 0; $d <$no_days; $d++) {
      echo "<td valign='top' style='padding:0px; text-align:center;font-family:Calibri,Arial;font-size:15px;font-weight:bold;width:".$colwid."px'>".$daylabels[$d]."<br/>\n";
      echo "  <table style='border-spacing:0px; border-collapse:collapse;'>\n";
      echo "    <tr><td style='padding:0px; width:".$colwid."px;height:1px;background-color:#404040'></td></tr>\n";

      $t1 = 0;
      $t2 = 0;
      while ($t1<$steps) {
        $current_type = $matrix[$d][$t1];
        while (($t2 < $steps-1) && ($matrix[$d][$t2+1]==$current_type)) $t2++;
        $height = (1+($t2-$t1))*(($rowhei/2)-1);
        $height = $height + ($t2-$t1);
        if ($current_type < 3) {
          echo "    <tr><td style='padding:0px; width:".$colwid."px;height:".$height."px;background-color:".$cols[$current_type]."'></td></tr>\n";
        } else {
          $cw1 = 0;
          $cw2 = 0;
          if ($current_type == $TT_OPEN_3) $cw1 = $colwid / 4; 
          else if ($current_type == $TT_OPEN_2) $cw1 = $colwid / 2; 
          else if ($current_type == $TT_OPEN_1) $cw1 = 3 * ($colwid / 4);
          $cw2 = $colwid - $cw1;
          echo "    <tr><td style='padding:0px; width:".$colwid."px;height:".$height."px;'><table cellpadding=\"0\" cellspacing=\"0\"><tr>";
          echo "      <td style='padding:0px; width:".$cw1."px;height:".$height."px;background-color:".$cols[$current_type]."'></td>\n";
          echo "      <td style='padding:0px; width:".$cw2."px;height:".$height."px;background-color:".$cols[1]."'></td>\n";
          echo "    </tr></table></td></tr>\n";
        }
        echo "    <tr><td style='padding:0px; width:".$colwid."px;height:1px;background-color:#404040'></td></tr>\n";
        $t1=$t2+1;
        $t2=$t1;
      }
      echo "  </table></td>\n";
      echo "<td style='padding:0px; background-color:#404040; width:1px'></td>\n"; 
    }
    echo "</tr><tr style='height:10px'><td></td></tr></table>\n";
  }
  function draw_tt_key() {
    $cols = array("#909090","#90d090","#9090d0");
    echo "<table><tr>\n";
    echo "  <td style=\"border:1px solid black;width:16px;height:16px;background:#90d090\"></td><td style=\"font-family:Calibri,Arial\">Open&nbsp;&nbsp;&nbsp;</td>\n";
    echo "  <td style=\"border:1px solid black;width:16px;height:16px;background:#9090d0\"></td><td style=\"font-family:Calibri,Arial\">School Use&nbsp;&nbsp;&nbsp;</td>\n";
    echo "  <td style=\"border:1px solid black;width:16px;height:16px;background:#909090\"></td><td style=\"font-family:Calibri,Arial\">Closed&nbsp;&nbsp;&nbsp;<br/></td>\n";
    echo "  <td style=\"border:1px solid black;width:16px;height:16px;background:#ff0000\"></td><td style=\"font-family:Calibri,Arial\">Lane Reserved&nbsp;&nbsp;&nbsp;<br/></td>\n";
    echo "</tr></table>&nbsp;<br/>\n";
  }
?>