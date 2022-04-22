<?php
include 'Print.php';
include 'Format.php';
include 'UserInput.php';
include 'Algorithms.php';

class SchedulingAlgorithm {
  private $sFileName           = '';
  private $nProcessors         = 0;
  private $arrComplexResults   = [];
  private $arrDirectories      = ["Instances", "Results"];
  private $arrInstance         = [];
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
    message("Hello!");

    foreach ($this->arrDirectories as $dir)
      if(!is_dir($dir)) mkdir($dir);

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
        message("$key: $value[0] " . ($value[1] === "X" ? '' : (" => " . ($value[1] === true ? "Yes" : ($value[1] === false ? 'No' : $value[1])))));
      
      // Ask for action and serve it
      $nCommand = askForNumber("What do you want to do?", [0, count($this->arrAlgorithmConfig)-1]);
      switch ($nCommand) {
        case 1:
          $this->arrAlgorithmConfig[1][1] = askForFileName();
          break;
        case 2:
          $this->arrAlgorithmConfig[2][1] = askQuestion("Do you want to sort the data first?");
          break;
        case 3:
          $this->arrAlgorithmConfig[3][1] = askForNumber("How many extra iterations?", [0, 99999]);
          break;
        case 4:
          $this->arrAlgorithmConfig[4][1] = askQuestion("Do you want to preserve data on elements processed by each processor?");
          break;
        case 5:
          $this->generateInstance();
          break;
        case 6:
          if ($this->arrAlgorithmConfig[1][1] == "Choose!")
            $this->arrAlgorithmConfig[1][1] = askForFileName();
          $this->runTest();
          break;
        case 7:
          showHistory($this->arrHistory);
          break;
        case 0:
          $bContinue = false;
          break;
        default:
          message("Wrong input! Try again!");
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
    message("Reading " . $this->sFileName . "...");
    
    // Populate data based on the file
    $this->arrInstance = file("Instances/" . $this->sFileName, FILE_IGNORE_NEW_LINES);
    $this->nProcessors = array_shift($this->arrInstance);
    $nTasks            = array_shift($this->arrInstance);

    message("Processors: $this->nProcessors; Tasks: $nTasks");
    
    /* 2. DECIDE IF DATA SHOULD BE SORTED FIRST */
    if ($bSortData)
      rsort($this->arrInstance);

    /* 3. CHOOSE TYPE OF TASK TO RUN */
    if ($bPreserveDetails) {
      for ($x = 0; $x <= $nIterations; $x++)
        $arrTime[] = runComplexAlgo($this->nProcessors, $this->arrInstance, $this->arrComplexResults);

      // If only a single run - propose displaying detailed output data
      if (!$nIterations)
        if (askQuestion("Do you want to display tasks assigned to each processor?"))
          printComplexResults($this->arrComplexResults);
    } else
      for ($x = 0; $x <= $nIterations; $x++)
        $arrTime[] = runSimpleAlgo($this->nProcessors, $this->arrInstance);

    /* 4. DISPLAY TIME RESULTS AND SAVE TO FILE */
    $this->displayAndSaveResults($bSortData, $nIterations, $bPreserveDetails, $arrTime);
  }

  function displayAndSaveResults($bSortData, $nIterations, $bPreserveDetails, $arrTime) {
    $nTimeAverage = round(array_sum($arrTime)/count($arrTime), 3);

    message("It took $nTimeAverage microseconds " . ($nIterations ? ('on average (based on ' . ($nIterations + 1) . ' iterations) ') : '') . "to process the data.", true);
    $this->saveToHistory($bSortData, $nIterations, $bPreserveDetails, $nTimeAverage);

    file_put_contents("Results/" . $this->sFileName . " Results " . ($nIterations + 1) . ".txt", implode(PHP_EOL, $arrTime));
  }

  /* Function generating the test instances based on user input */
  function generateInstance() {
    $arrResult   = [];
    $nProcessors = askForNumber("How many processors?");
    $nTasks      = askForNumber("How many Tasks?");
    $nShortest   = askForNumber("Shortest task time?");
    $nLongest    = askForNumber("Longest task time?");
    
    $arrResult[0] = $nProcessors;
    $arrResult[1] = $nTasks;

    // Generate random times for each task
    for ($x = 0; $x < $nTasks; $x++)
      $arrResult[] = rand($nShortest, $nLongest);

    $sGeneratedFilename = sprintf('Instance p%s t%s %s-%s.txt', $nProcessors, $nTasks, $nShortest, $nLongest);

    file_put_contents("Instances/" . $sGeneratedFilename, implode(PHP_EOL, $arrResult));

    if (askQuestion("Would you like to load this file for testing?"))
      $this->arrAlgorithmConfig[1][1] = $sGeneratedFilename;
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

}

// Run main function
$algo = new SchedulingAlgorithm();
$algo->main();