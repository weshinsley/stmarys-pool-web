<?php

  // The status file is a single-line, comma-separated set of values.
  // the MSG and TT_NAME are base64_encoded

  $FS_TYPE = 0;
  $FS_LANES = 1;
  $FS_SWIMMERS = 2;
  $FS_MSGON = 3;
  $FS_MSG = 4;
  $FS_TT_NAME = 5;
  $FS_TT_NAME_ENC = 6;

  $FS_AUTO = "AUTO";
  $FS_OPEN = "OPEN";
  $FS_CLOSED = "CLOSED";
  $FS_SCHOOL = "SCHOOL";

  function readStatus($root) {
    global $FS_MSG, $FS_TT_NAME, $FS_TT_NAME_ENC;
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