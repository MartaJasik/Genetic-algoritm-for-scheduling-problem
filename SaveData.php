<?php

  function displayAndSaveResults(&$arrHistory, $arrComplexResults, $sFilename, $bSortData, $nIterations, $bPreserveDetails, $arrTime) {
    // If only a single run - propose displaying detailed output data
    if (!$nIterations)
      if (askQuestion("Do you want to display tasks assigned to each processor?"))
        printComplexResults($arrComplexResults);
    
    $nTimeAverage = round((array_sum($arrTime)/count($arrTime)) * 1000000, 3);
    $nMaxLength   = max(array_column($arrComplexResults, 0));
    message("It took $nTimeAverage microseconds " . ($nIterations ? ('on average (based on ' . ($nIterations + 1) . ' iterations) ') : '') . "to process the data. Max length is $nMaxLength.", true);
   
    // Keep only 30 latest results
    if (count($arrHistory) >= 30)
      array_shift($arrHistory);

    $arrHistory[] = ['Timestamp' => time(), 
                     'Filename'  => $sFilename,  
                     'Sort'      => $bSortData, 
                     'ExtraRuns' => $nIterations, 
                     'Detailed'  => $bPreserveDetails, 
                     'Tmax'      => $nMaxLength, 
                     'Time'      => $nTimeAverage];

    file_put_contents("History.json", json_encode($arrHistory));

    file_put_contents("Results/Results " . $sFilename . " I" . ($nIterations + 1) . ".txt", implode(PHP_EOL, $arrTime));
  }
