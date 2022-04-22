<?php

  /* Simple function for cleaner message formatting */
  function message($sMessage = '', $bSpecial = false) {
      $sMessage = "*** $sMessage" . PHP_EOL;
      if ($bSpecial)
        echo "******" . PHP_EOL . "****$sMessage********" . PHP_EOL . str_repeat("*", 100) . PHP_EOL . PHP_EOL;
      else
        echo $sMessage;
  }