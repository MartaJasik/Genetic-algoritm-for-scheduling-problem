<?php

  /* Complex alghoritm, preserves which tasks went to which processor (on top of providing the execution time) */
  function runComplexAlgo($nProcessors, $arrTasks, &$arrComplexResults) {
    $nStart = microtime(true);
    for ($x = 0; $x <= $nProcessors; $x++) 
        $arrProcessors[] = [0, []];
      
    foreach ($arrTasks as $nTask) {
      $nKey = array_search(min(array_column($arrProcessors, 0)), array_column($arrProcessors, 0));
      $arrProcessors[$nKey][0] += $nTask;
      array_push($arrProcessors[$nKey][1], $nTask);
    }
    $arrComplexResults = $arrProcessors;
    return (microtime(true) - $nStart) * 1000000;
  }

  /* Simple alghoritm, doesn't preserve which tasks went to which processor (cares only about execution time) */
  function runSimpleAlgo($nProcessors, $arrTasks) {
    $nStart = microtime(true);
    for ($x = 0; $x <= $nProcessors; $x++) 
      $arrProcessors[] = 0;
  
    foreach ($arrTasks as $nTask) {
      $nKey = array_search(min($arrProcessors), $arrProcessors);
      $arrProcessors[$nKey] += $nTask;
    }
    return (microtime(true) - $nStart) * 1000000;
  }

  function runAlgorithm() {

  }
