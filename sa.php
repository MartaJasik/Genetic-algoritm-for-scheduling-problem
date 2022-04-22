<?php

class SchedulingAlgorithm {
  private $nProcessors         = 0;
  private $arrComplexResults   = [];
  private $arrInstance         = [];
  private $sFileName           = '';
  private $arrHistory          = [];
  private $arrAlgorithmConfig  = [1 => ["Data file            ", 'Choose!'],
                                  2 => ["Data sorting         ", true],
                                  3 => ["Extra iterations     ", 0],
                                  4 => ["Preserve data details", true],
                                  5 => ["Generate instances...", 'X'],
                                  6 => ["Run tests!           ", 'X'],
                                  7 => ["Show history...      ", 'X'],
                                  0 => ["Quit                 ", 'X']];
  
  /* Function used to run the program */
  function main() {
    $this->message("Hello!");

    // Load history
    if (file_exists("History.json"))
      $this->arrHistory = json_decode(file_get_contents("History.json"), true);

    $this->printMenu();
  }

  /* Function printing the menu and serving user choices */
  function printMenu() {
    $bContinue = true;
    while ($bContinue) {
      // Print menu elements
      foreach ($this->arrAlgorithmConfig as $key => $value) 
        $this->message("$key: $value[0] " . ($value[1] === "X" ? '' : (" => " . ($value[1] === true ? "Yes" : ($value[1] === false ? 'No' : $value[1])))));
      
      // Ask for action and serve it
      $nCommand = $this->askForNumber("What do you want to do?", [0, count($this->arrAlgorithmConfig)-1]);
      switch ($nCommand) {
        case 1:
          $this->arrAlgorithmConfig[1][1] = $this->askForFileName();
          break;
        case 2:
          $this->arrAlgorithmConfig[2][1] = $this->askQuestion("Do you want to sort the data first?");
          break;
        case 3:
          $this->arrAlgorithmConfig[3][1] = $this->askForNumber("How many extra iterations?", [0, 99999]);
          break;
        case 4:
          $this->arrAlgorithmConfig[4][1] = $this->askQuestion("Do you want to preserve data on elements processed by each processor?");
          break;
        case 5:
          $this->generateInstance();
          break;
        case 6:
          if ($this->arrAlgorithmConfig[1][1] == "Choose!")
            $this->arrAlgorithmConfig[1][1] = $this->askForFileName();
          $this->runTest();
          break;
        case 7:
          $this->showHistory();
          break;
        case 0:
          $bContinue = false;
          break;
        default:
          $this->message("Wrong input! Try again!");
          break;
      }
    }
  }

  /* Function running the algorithms on given sets of data */
  function runTest() {
    $this->sFileName  = $this->arrAlgorithmConfig[1][1];
    $bSortData        = $this->arrAlgorithmConfig[2][1];
    $nIterations      = $this->arrAlgorithmConfig[3][1];
    $bPreserveDetails = $this->arrAlgorithmConfig[4][1];
    $arrTime          = [];

    /* 1. READ FILE  */   
    $this->message("Reading $this->sFileName...");
    
    // Populate data based on the file
    $this->arrInstance = file($this->sFileName, FILE_IGNORE_NEW_LINES);
    $this->nProcessors = array_shift($this->arrInstance);
    $nTasks            = array_shift($this->arrInstance);

    $this->message("Processors: $this->nProcessors; Tasks: $nTasks");
    
    /* 2. DECIDE IF DATA SHOULD BE SORTED FIRST */
    if ($bSortData)
      rsort($this->arrInstance);

    /* 3. CHOOSE TYPE OF TASK TO RUN */
    if ($bPreserveDetails) {
      for ($x = 0; $x <= $nIterations; $x++)
        $arrTime[] = $this->runComplexAlgo();

      // If only a single run - propose displaying detailed output data
      if (!$nIterations)
        if ($this->askQuestion("Do you want to display tasks assigned to each processor?"))
          $this->printComplexResults();
    } else
      for ($x = 0; $x <= $nIterations; $x++)
        $arrTime[] = $this->runSimpleAlgo();

    /* 4. DISPLAY TIME RESULTS AND SAVE TO FILE */
    $this->displayAndSaveResults($bSortData, $nIterations, $bPreserveDetails, $arrTime);
  }

  function displayAndSaveResults($bSortData, $nIterations, $bPreserveDetails, $arrTime) {
    $nTimeAverage = round(array_sum($arrTime)/count($arrTime), 3);

    $this->message("It took $nTimeAverage microseconds " . ($nIterations ? ('on average (based on ' . ($nIterations + 1) . ' iterations) ') : '') . "to process the data.", true);
    $this->saveToHistory($bSortData, $nIterations, $bPreserveDetails, $nTimeAverage);

    /* Uncomment if you want to save time results of each run */
    // file_put_contents($this->sFileName . " Results " . ($nIterations + 1) . ".txt", implode(PHP_EOL, $arrTime));
  }

  /* Function generating the test instances based on user input */
  function generateInstance() {
    $arrResult   = [];
    $nProcessors = $this->askForNumber("How many processors?");
    $nTasks      = $this->askForNumber("How many Tasks?");
    $nShortest   = $this->askForNumber("Shortest task time?");
    $nLongest    = $this->askForNumber("Longest task time?");
    
    $arrResult[0] = $nProcessors;
    $arrResult[1] = $nTasks;

    // Generate random times for each task
    for ($x = 0; $x < $nTasks; $x++)
      $arrResult[] = rand($nShortest, $nLongest);

    $sGeneratedFilename = sprintf('Instance p%s t%s %s-%s.txt', $nProcessors, $nTasks, $nShortest, $nLongest);

    file_put_contents($sGeneratedFilename, implode(PHP_EOL, $arrResult));

    if ($this->askQuestion("Would you like to load this file for testing?"))
      $this->arrAlgorithmConfig[1][1] = $sGeneratedFilename;
  }
  
  /* Complex alghoritm, preserves which tasks went to which processor (on top of providing the execution time) */
  function runComplexAlgo() {
    $nStart = microtime(true);
    for ($x = 0; $x <= $this->nProcessors; $x++) 
        $arrProcessors[] = [0, []];
      
    foreach ($this->arrInstance as $nTask) {
      $nKey = array_search(min(array_column($arrProcessors, 0)), array_column($arrProcessors, 0));
      $arrProcessors[$nKey][0] += $nTask;
      array_push($arrProcessors[$nKey][1], $nTask);
    }
    $this->arrComplexResults = $arrProcessors;
    return (microtime(true) - $nStart) * 1000000;
  }

  /* Simple alghoritm, doesn't preserve which tasks went to which processor (cares only about execution time) */
  function runSimpleAlgo() {
    $nStart = microtime(true);
    for ($x = 0; $x <= $this->nProcessors; $x++) 
      $arrProcessors[] = 0;
  
    foreach ($this->arrInstance as $nTask) {
      $nKey = array_search(min($arrProcessors), $arrProcessors);
      $arrProcessors[$nKey] += $nTask;
    }
    return (microtime(true) - $nStart) * 1000000;
  }

  /* Function for getting the filename from user. Checks if 'x' (default) or other proper path provided, repeats otherwise. */
  function askForFileName() {
    while (true) {
      $arrFiles         = glob("*.txt");
      // Filter out the "Results" files
      $arrFilteredFiles = array_values(array_filter($arrFiles, function ($var) { return (stripos($var, 'Results') === false); }));

      // Print found files
      foreach ($arrFilteredFiles as $key => $value)
        $this->message($key . ": " .  $value);

      $nChosenKey = $this->askForNumber("Which file do you want to use?", [0, count($arrFilteredFiles)-1]);
      
      if (is_file($arrFilteredFiles[$nChosenKey]))
        return $arrFilteredFiles[$nChosenKey];
      else
        $this->message("Wrong file! Try again!");
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
          $this->message("Wrong input! Try again!");
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
        $this->message("Wrong input (not an integer between $arrRange[0] - $arrRange[1])! Try again!");
    }
  }
  
  /* Save results to program & file history */
  function saveToHistory($bSortData, $nIterations, $bPreserveDetails, $sTimeAverage) {
    // Keep only 30 latest results
    if (count($this->arrHistory) >= 30)
      array_shift($this->arrHistory);

    $this->arrHistory[] = ['Timestamp'  => time(), 
                           'Filename'   => $this->sFileName,  
                           'Sort'       => $bSortData, 
                           'ExtraRuns'  => $nIterations, 
                           'Detailed'   => $bPreserveDetails, 
                           'Time'       => $sTimeAverage];

    file_put_contents("History.json", json_encode($this->arrHistory));
  }

  /* Display formatted history */
  function showHistory() {
    $arrHistory = $this->arrHistory;
    $arrLengths = [];
    $arrHeaders = ['Timestamp'  => "Date", 
                   'Filename'   => "File",  
                   'Sort'       => "Sorted", 
                   'ExtraRuns'  => "Extra Runs", 
                   'De2tailed'  => "Keep Details", 
                   'Time'       => "Time [μs]"];

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

  /* Simple function for cleaner message formatting */
  function message($sMessage = '', $bSpecial = false) {
    $sMessage = "*** $sMessage" . PHP_EOL;
    if ($bSpecial)
      echo "******" . PHP_EOL . "****$sMessage********" . PHP_EOL . str_repeat("*", 100) . PHP_EOL . PHP_EOL;
    else
      echo $sMessage;
  }

  /* Print complex results - tasks assigned for each processor */
  function printComplexResults() {
    for ($x = 0; $x < count($this->arrComplexResults); $x++) 
      $this->message("(" . str_repeat("0", strlen((string)count($this->arrComplexResults))-strlen((string)$x+1)) . ($x+1) 
                   . ") Length: " . $this->arrComplexResults[$x][0] . "; Elements: " . json_encode($this->arrComplexResults[$x][1]));
  }


}

// Run main function
$algo = new SchedulingAlgorithm();
$algo->main();