<?php

  // The status file is a single-line, comma-separated set of values.
  // the MSG and TT_NAME are base64_encoded

  $FS_TYPE = 0;
  $FS_LANES = 1;
  $FS_SWIMMERS = 2;
  $FS_MSGON = 3;
  $FS_MSG = 4;
  $FS_TT_NAME = 5;
  $FS_PTEMP = 6;
  $FS_ATEMP = 7;
  $FS_TT_NAME_ENC = 8;
  
  $FS_AUTO = "AUTO";
  $FS_OPEN = "OPEN";
  $FS_CLOSED = "CLOSED";
  $FS_SCHOOL = "SCHOOL";

  $FS_ALL_STATUSES = array($FS_AUTO, $FS_OPEN, $FS_CLOSED, $FS_SCHOOL);
  $FS_ALL_STATUS_NAMES = array("Automatic (uses timetable)","Force Open","Force Closed","Force School Use");

  function readStatus($root) {
    global $FS_MSG, $FS_TT_NAME, $FS_TT_NAME_ENC, $FS_PTEMP, $FS_ATEMP;
    $fstatus = "";
    $fl = fopen($root."data/status.lck", "r");
    if (flock($fl, LOCK_SH)) {
      $fp = fopen($root."data/status.txt", "r");
      $fstatus = fgets($fp);
      fclose($fp);
      flock($fl, LOCK_UN);
    }
    fclose($fl);
    $fstatus = explode(",", $fstatus);
    $fstatus[$FS_MSG] = base64_decode($fstatus[$FS_MSG]);
    $fstatus[$FS_TT_NAME_ENC] = $fstatus[$FS_TT_NAME];
    $fstatus[$FS_TT_NAME] = base64_decode($fstatus[$FS_TT_NAME]);
    $fstatus[$FS_PTEMP] = $fstatus[$FS_PTEMP];
    $fstatus[$FS_ATEMP] = $fstatus[$FS_ATEMP];
    return $fstatus;
  }

  function writeStatus($root, $str) {
    $fl = fopen("../data/status.lck", "w");
    if (flock($fl, LOCK_EX)) {
      $fp = fopen("../data/status.txt", "w");
      fputs($fp,$str);
      fclose($fp);
      flock($fl, LOCK_UN);
    }
    fclose($fl);
  }
?>