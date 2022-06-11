<?php
include 'PrintData.php';
include 'SaveData.php';
include 'Format.php';
include 'UserInput.php';
include 'Algorithms.php';

class SchedulingAlgorithm {
  private $arrHistory          = [];
  private $arrDirectories      = ["Instances", "Results"];
  private $arrAlgorithmConfig  = [1 => ["Data file            ", 'Choose!'],
                                  2 => ["Data sorting         ", true],
                                  3 => ["Extra iterations     ", 0],
                                  4 => ["Genetic algorithm    ", true],
                                  5 => ["Generate instances...", 'X'],
                                  6 => ["Run tests!           ", 'X'],
                                  7 => ["Show history...      ", 'X'],
                                  0 => ["Quit                 ", 'X']];
  
  /* Function used to run the program */
  function main() {
    message("Hello!");

    // Create directories if not there
    foreach ($this->arrDirectories as $dir)
      if(!is_dir($dir)) mkdir($dir);

    // Load history
    if (file_exists("History.json"))
      $this->arrHistory = json_decode(file_get_contents("History.json"), true);

    $this->showMenu();
  }

  /* Function printing the menu and serving user choices */
  function showMenu() {
    $bContinue = true;

    while ($bContinue) {
      // Print menu elements
      foreach ($this->arrAlgorithmConfig as $key => $value) 
        message("$key: $value[0] " . ($value[1] === "X" ? '' : (" => " . ($value[1] === true ? "Yes" : ($value[1] === false ? 'No' : $value[1])))));
      
      // Ask for action choice and serve it
      $nCommand = askForNumber("What do you want to do?", [0, count($this->arrAlgorithmConfig) - 1]);
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
          $this->arrAlgorithmConfig[4][1] = askQuestion("Do you want to run a genetic algorithm? (No = greedy one)");
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
    $sFileName         = $this->arrAlgorithmConfig[1][1];
    $bSortData         = $this->arrAlgorithmConfig[2][1];
    $nIterations       = $this->arrAlgorithmConfig[3][1];
    $bGeneticAlgo      = $this->arrAlgorithmConfig[4][1];
    $arrRunResults = [];
    $arrTime           = [];

    /* 1. READ FILE  */   
    message("Reading " . $sFileName . "...");
    
    // Populate data based on the file
    $arrInstance = file("Instances/" . $sFileName, FILE_IGNORE_NEW_LINES);
    $nProcessors = array_shift($arrInstance);
    $nTasks      = array_shift($arrInstance);

    message("Processors: $nProcessors; Tasks: $nTasks");
    
    /* 2. DECIDE IF DATA SHOULD BE SORTED FIRST */
    if ($bSortData)
      rsort($arrInstance);

    /* 3. CHOOSE TYPE OF TASK TO RUN */
    if ($bGeneticAlgo) {
      $arrInitialPopulation = generateInitialPopulation($arrInstance, 20, 50);
      for ($x = 0; $x <= $nIterations; $x++) {
        $nTime = runGeneticAlgo($nProcessors, $arrInstance, 20, 50, $arrInitialPopulation);
        message($x . ": Min pop length is $nTime.");
      }
    } else
      for ($x = 0; $x <= $nIterations; $x++)
        $arrTime[] = runGreedyAlgo($nProcessors, $arrInstance, $arrRunResults);

    /* 4. DISPLAY TIME RESULTS AND SAVE TO FILE */
    displayAndSaveResults($this->arrHistory, $arrRunResults, $sFileName, $bSortData, $nIterations, $bGeneticAlgo, $arrTime);
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
}

// Run main function
$algo = new SchedulingAlgorithm();
$algo->main();