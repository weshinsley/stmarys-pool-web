<?php
  function do_num_slider($num,$min,$max,$upfunc,$downfunc) {
  $string = "";
  if ($num>$min) {
    $string.= "<a href='javascript:".$downfunc."'><img border='0' src='../images/minus.png' alt='' /></a>&nbsp;&nbsp;&nbsp;";
  } else {          
    $string.= "<img src='../images/minusgrey.png' alt='' />&nbsp;&nbsp;&nbsp;";
  }
  $string.=$num;
  if ($num<$max) {
    $string.= "&nbsp;&nbsp;&nbsp;<a href='javascript:".$upfunc."'><img border='0' src='../images/plus.png' alt='' /></a>";
  } else {
    $string.= "&nbsp;&nbsp;&nbsp;<img src='../images/plusgrey.png' alt='' />";
  }
  return $string;
}
?>