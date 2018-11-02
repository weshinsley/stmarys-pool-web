<?php

  // Useful shared functions for handling the timetable XML file.
  
  $TT_OPEN = "open";
  $TT_SCHOOL = "school";

  $TT_F_DUP = 1;
  $TT_F_RENAME = 2;
  $TT_F_DELETE = 3;
  $TT_F_SAVE = 4;

  function readTimetable($root) {
    $fp = fopen($root."data/tt.lck", "r");
    $tts = null;
    if (flock($fp, LOCK_SH)) {
      $tts  =  simplexml_load_file($root.'data/tt.xml');
      flock($fp, LOCK_UN);
    }
    fclose($fp);
    return $tts;
  }

  function rename_tt($root, $tts, $orig, $new) {
    $fp = fopen($root."data/tt.lck", "r");
    if (flock($fp, LOCK_EX)) {
      $fw = fopen($root."data/tt.xml","w");
      fwrite($fw, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tts>\n");
      foreach ($tts->tt as $tt) {
        fwrite($fw,"  <tt name=\"");
        if ($tt['name']==$orig) fwrite($fw, $new);
        else fwrite($fw, $tt['name']);
        fwrite($fw, "\">");
        foreach ($tt->slot as $slot) {
          fwrite($fw, "    <slot type=\"".$slot['type']."\" day=\"".$slot['day']."\" start=\"".$slot['start']."\" end=\"".$slot['end']."\" />\n");
        }
        fwrite($fw, "  </tt>\n");
      }
      fwrite($fw, "</tts>\n");
      fclose($fw);
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

  function delete_tt($root, $tts, $todel) {
    $fp = fopen($root."data/tt.lck", "r");
    if (flock($fp, LOCK_EX)) {
      $fw = fopen($root."data/tt.xml","w");
      fwrite($fw, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tts>\n");
      foreach ($tts->tt as $tt) {
        if ($tt['name']!=$todel) {
          fwrite($fw,"  <tt name=\"".$tt['name']."\">");
          foreach ($tt->slot as $slot) {
            fwrite($fw, "    <slot type=\"".$slot['type']."\" day=\"".$slot['day']."\" start=\"".$slot['start']."\" end=\"".$slot['end']."\" />\n");
          }
          fwrite($fw, "  </tt>\n");
        }
      }
      fwrite($fw, "</tts>\n");
      fclose($fw);
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

  function write_slots($fw, $data) {
    $days = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
    for ($i=0; $i<count($data); $i+=4) {
      $day = $data[$i];
      $type = $data[$i+1];
      $start = $data[$i+2];
      $end = $data[$i+3];
      fwrite($fw,"    <slot day=\"".$days[$day]."\" type=\"".$type."\" start=\"".$start."\" end=\"".$end."\"/>\n");
    }
  }
  
  function add_tt($root, $tts, $ttn, $data) {
    $fp = fopen($root."data/tt.lck", "r");
    if (flock($fp, LOCK_EX)) {
      $fw = fopen($root."data/tt.xml","w");
      fwrite($fw, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tts>\n");
      foreach ($tts->tt as $tt) {
        fwrite($fw,"  <tt name=\"".$tt['name']."\">\n");
        foreach ($tt->slot as $slot) {
          fwrite($fw, "    <slot type=\"".$slot['type']."\" day=\"".$slot['day']."\" start=\"".$slot['start']."\" end=\"".$slot['end']."\" />\n");
        }
        fwrite($fw, "  </tt>\n");
      }
      fwrite($fw,"  <tt name=\"".$ttn."\">\n");
      write_slots($fw, $data);
      fwrite($fw,"  </tt>\n");
      fwrite($fw, "</tts>\n");
      fclose($fw);
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

  function save_tt($root, $tts, $ttn, $data) {
    $fp = fopen($root."data/tt.lck", "r");
    if (flock($fp, LOCK_EX)) {
      $fw = fopen($root."data/tt.xml","w");
      fwrite($fw, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<tts>\n");
      foreach ($tts->tt as $tt) {
        fwrite($fw,"  <tt name=\"".$tt['name']."\">");
        if ($tt['name'] == $ttn) {
          write_slots($fw, $data);
        } else {
          foreach ($tt->slot as $slot) {
            fwrite($fw, "    <slot type=\"".$slot['type']."\" day=\"".$slot['day']."\" start=\"".$slot['start']."\" end=\"".$slot['end']."\" />\n");
          }
        }
        fwrite($fw, "  </tt>\n");
      }
      fwrite($fw, "</tts>\n");
      fclose($fw);
      flock($fp, LOCK_UN);
    }
    fclose($fp);
  }

?>