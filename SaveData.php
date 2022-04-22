<?php

  function displayAndSaveResults(&$arrHistory, $sFilename, $bSortData, $nIterations, $bPreserveDetails, $arrTime) {
    $nTimeAverage = round((array_sum($arrTime)/count($arrTime)) * 1000000, 3);

    message("It took $nTimeAverage microseconds " . ($nIterations ? ('on average (based on ' . ($nIterations + 1) . ' iterations) ') : '') . "to process the data.", true);
   
    // Keep only 30 latest results
    if (count($arrHistory) >= 30)
      array_shift($arrHistory);

    $arrHistory[] = ['Timestamp'  => time(), 
                     'Filename'   => $sFilename,  
                     'Sort'       => $bSortData, 
                     'ExtraRuns'  => $nIterations, 
                     'Detailed'   => $bPreserveDetails, 
                     'Time'       => $nTimeAverage];

    file_put_contents("History.json", json_encode($arrHistory));

    file_put_contents("Results/Results " . $sFilename . " I" . ($nIterations + 1) . ".txt", implode(PHP_EOL, $arrTime));
  }
