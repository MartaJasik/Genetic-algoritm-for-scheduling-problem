<?php


  /* Function for getting the filename from user. Checks if 'x' (default) or other proper path provided, repeats otherwise. */
  function askForFileName() {
    while (true) {
      $arrFiles = glob("Instances/*.txt");

      // Print found files
      foreach ($arrFiles as $key => $value)
        message($key . ": " . $value);

      $nChosenKey = askForNumber("Which file do you want to use?", [0, count($arrFiles)-1]);
      
      if (is_file($arrFiles[$nChosenKey]))
        return basename($arrFiles[$nChosenKey]);
      else
        message("Wrong file! Try again!");
    }
  }

  /* Function for asking questions and receiving Y / N answers from terminal. Returns true if Y, false if N, repeats otherwise. */
  function askQuestion($sQuestion, $sTrue = "Y", $sFalse = "N") {
    while (true) {
      $a = strtoupper(readline("$sQuestion $sTrue / $sFalse" . PHP_EOL));
      switch ($a) {
        case $sTrue:
          return true;
        case $sFalse:
          return false;
        default:
          message("Wrong input! Try again!");
          break;
      }
    }
  }

  /* Function asking user for a number between 1-3000. */
  function askForNumber($sQuestion, $arrRange = [1,3000]) {
    while (true) {
      $a = readline($sQuestion . ' (' . $arrRange[0] . '-' . $arrRange[1] . ')' . PHP_EOL);
      if (is_numeric($a) && $a >= $arrRange[0] && $a <= $arrRange[1])
        return (int)$a;
      else
        message("Wrong input (not an integer between $arrRange[0] - $arrRange[1])! Try again!");
    }
  }
  