<?php

  /* Print complex results - tasks assigned for each processor */
  function printComplexResults($arrComplexResults) {
    for ($x = 0; $x < count($arrComplexResults); $x++) 
      message("(" . str_repeat("0", strlen((string)count($arrComplexResults))-strlen((string)$x+1)) . ($x+1) 
                   . ") Length: " . $arrComplexResults[$x][0] . "; Elements: " . json_encode($arrComplexResults[$x][1]));
  }

  /* Display formatted history */
  function showHistory($arrHistory) {
    $arrLengths = [];
    $arrHeaders = ['Timestamp' => "Date", 
                   'Filename'  => "File",  
                   'Sort'      => "Sorted", 
                   'ExtraRuns' => "Extra Runs", 
                   'Detailed'  => "Keep Details", 
                   'Tmax'      => "Tmax", 
                   'Time'      => "Time [μs]"];
    // Format data for clean display
    foreach($arrHistory as $key => $value) {
      $arrHistory[$key]['Timestamp'] = date("m/d H:i:s", ($arrHistory[$key]['Timestamp'] + 7200)); 
      $arrHistory[$key]['Sort']      = $arrHistory[$key]['Sort'] == 1 ? "Yes" : "No"; 
      $arrHistory[$key]['Detailed']  = $arrHistory[$key]['Detailed'] == 1 ? "Yes" : "No"; 
    }
    array_unshift($arrHistory, $arrHeaders);
    
    // Collect max lengths of each column for nice table formatting
    foreach ($arrHistory[0] as $key => $value)
      $arrLengths[$key] = max(array_map('strlen', array_column($arrHistory, $key)));
    echo str_repeat("-", 100) . PHP_EOL;
    foreach ($arrHistory as $row) {
      foreach ($row as $key => $value)
        echo $value . str_repeat(" ", ($arrLengths[$key] - strlen($value) + 2));
      echo PHP_EOL;
    }

    echo str_repeat("-", 100) . PHP_EOL . PHP_EOL;
  }