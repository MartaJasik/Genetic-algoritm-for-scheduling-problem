<?php

  /* Greedy alghoritm, preserves which tasks went to which processor (on top of providing the execution time) */
  function runGreedyAlgo($nProcessors, $arrTasks, &$arrRunResults) {
    $nStart = microtime(true);
    for ($x = 0; $x < $nProcessors; $x++) 
      $arrProcessors[] = [0, []];
      
    foreach ($arrTasks as $nTask) {
      $arrCol                   = array_column($arrProcessors, 0);
      $nKey                     = array_search(min($arrCol), $arrCol);
      $arrProcessors[$nKey][0] += $nTask;
      array_push($arrProcessors[$nKey][1], $nTask);
    }
    $arrRunResults = $arrProcessors;

    message(json_encode($arrProcessors));

    return (microtime(true) - $nStart);
  }

  function runSimpleGreedy($arrProcessors, $arrTasks) {
  
    foreach ($arrTasks as $nTask) {
      $nKey                  = array_search(min($arrProcessors), $arrProcessors);
      $arrProcessors[$nKey] += $nTask;
    }

    return max($arrProcessors);
  }


  /* Genetic alghoritm, preserves which tasks went to which processor (on top of providing the execution time) */
  function runGeneticAlgo($nProcessors, $arrTasks, $nPopSize, $nPopMixFactor, &$arrPopulation) {
    $arrRunResults = [];
    $nStart = microtime(true);
    $nNumOfTasks = count($arrTasks);
    
    // Create universal processor table
    for ($x = 0; $x < $nProcessors; $x++) 
      $arrProcessors[] = 0;

    foreach ($arrPopulation as $arrSpecimen)
      $arrRunResults[] = [runSimpleGreedy($arrProcessors, $arrSpecimen), $arrSpecimen];
  
    // sort specimens by best times
    usort($arrRunResults, "sortByIndexZero");

    // move the best specimen to the next population right away
    $arrNewPopulation[] = $arrRunResults[0][1];

    // populate new population
    for ($x = 0; $x < $nPopSize-1; $x++) {
      // take two parents from first half of last population results
      $arrTwoRandParentKeys = array_rand(range(0, floor(count($arrRunResults)/2)), 2);
  
      $arrParent1 = $arrRunResults[$arrTwoRandParentKeys[0]][1];
      $arrParent2 = $arrRunResults[$arrTwoRandParentKeys[1]][1];
  
      $arrAvailableIndexes = range(0, $nNumOfTasks-1);
      $arrTwoRandKeys      = array_rand($arrAvailableIndexes, 2);

      $arrNewPopulation[] = crossover($arrParent1, $arrParent2, min($arrTwoRandKeys), max($arrTwoRandKeys));
    }

    foreach ($arrNewPopulation as $arrSpecimen) {
      $arrRunResults[] = [runSimpleGreedy($arrProcessors, $arrSpecimen), $arrSpecimen];
    }
    $arrPopulation =$arrNewPopulation;
/*     foreach ($arrInitialPopulation as $pop)
      echo json_encode($pop) . PHP_EOL;
*/
   // echo json_encode($arrRunResults);


    return min(array_column($arrRunResults, 0));
  }


  function generateInitialPopulation($arrTasks, $nPopSize, $nPopMixFactor) {
    $nNumOfTasks = count($arrTasks);
    $nTasksToMix = floor($nNumOfTasks * ($nPopMixFactor / 100) / 2);
    $arrInitialPopulation[] = $arrTasks; 
    
    for ($x = 1; $x < $nPopSize; $x++) 
      $arrInitialPopulation[$x] = mixSpecimen($arrInitialPopulation[$x-1], $nTasksToMix, $nNumOfTasks);

    return $arrInitialPopulation;
  }

  function mixSpecimen($arrTasks, $nTasksToMix, $nNumOfTasks) {
    $arrAvailableIndexes = range(0, $nNumOfTasks-1);
    $arrRandomMixes = [];
    $arrNewSpecimen = $arrTasks;

    for ($x = 0; $x < $nTasksToMix; $x++) {
      $arrTwoRandKeys = array_rand($arrAvailableIndexes, 2);
      foreach($arrTwoRandKeys as $key)
        unset($arrAvailableIndexes[$key]);

      $arrRandomMixes[$x] = $arrTwoRandKeys;
    }

    foreach ($arrRandomMixes as $mix)
      [$arrNewSpecimen[$mix[0]], $arrNewSpecimen[$mix[1]]] = [$arrNewSpecimen[$mix[1]], $arrNewSpecimen[$mix[0]]];
  
    return $arrNewSpecimen;
  }

  function sortByIndexZero($a,$b) {
    if ($a[0] == $b[0]) {
        return 0;
    }
    return ($a[0] < $b[0]) ? -1 : 1;
}


function crossover($arrParent1, $arrParent2, $nStartIndex, $nEndIndex) {
  $newChild = array_fill(0, count($arrParent1), 0);;
  $nLength = $nEndIndex - $nStartIndex + 1;
  $arrInterval1 = array_slice($arrParent1, $nStartIndex, $nLength);
  $arrInterval2 = array_slice($arrParent2, $nStartIndex, $nLength);

  $arrParent2Rest = [];
  for ($x = 0; $x <= count($arrParent2); $x++) {
    
  }

  $arrParent2Rest  = array_slice($arrParent2, 0, $nStartIndex);

  foreach (array_slice($arrParent2, $nEndIndex+1) as $el)
    array_push($arrParent2Rest, $el);

/*     echo 'parent1: ' . json_encode($arrParent1) . PHP_EOL;
    echo 'parent2: ' . json_encode($arrParent2) . PHP_EOL;
    echo 'nStartIndex: ' . json_encode($nStartIndex) . PHP_EOL;
    echo 'nEndIndex: ' . json_encode($nEndIndex) . PHP_EOL;
    echo 'arrInterval1: ' . json_encode($arrInterval1) . PHP_EOL;
    echo 'arrInterval2: ' . json_encode($arrInterval2) . PHP_EOL;
    echo 'arrParent2Rest: ' . json_encode($arrParent2Rest) . PHP_EOL; */

  foreach ($arrInterval1 as $i) {
    if (in_array($i, $arrParent2Rest)) {
      if (($key = array_search($i, $arrParent2Rest)) !== false) {
        unset($arrParent2Rest[$key]);
      }
    }
    else if (in_array($i, $arrInterval2)) {
      if (($key = array_search($i, $arrInterval2)) !== false) {
        unset($arrInterval2[$key]);
      }
    }
  }

  $i = 0;
  for ($x = $nStartIndex; $x <= $nEndIndex; $x++) { 
    $newChild[$x] = $arrInterval1[$i];
    $i++;
  }
  $i = 0;

/*     echo 'child2: ' . json_encode($newChild) . PHP_EOL;
    echo 'arrInterval2 B: ' . json_encode($arrInterval2) . PHP_EOL;
    echo 'arrParent2Rest B: ' . json_encode($arrParent2Rest) . PHP_EOL; */
  //hack. not right way.
  $arrRemainingVals = $arrInterval2;

  foreach ($arrParent2Rest as $el)
    array_push($arrRemainingVals, $el);

  for ($x = 0; $x < count($arrParent1); $x++) { 
    if ($newChild[$x] == 0)
      $newChild[$x] = array_shift($arrRemainingVals);
  }
 // echo 'child2 B: ' . json_encode($newChild) . PHP_EOL;
  return $newChild;
}
