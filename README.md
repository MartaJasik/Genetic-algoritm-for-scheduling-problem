# A genetic alghoritm implementation for solving the task scheduling problem

This is a console-based PHP application, implementing both greedy and genetic alghoritms to solve the task scheduling problem.

## Fuctionalities
Simple console interface lets you choose from the available options, without any extra commands needed.

Possibility to:
* choose Greedy or Genetic alghoritm
* select a specific instance file
* decide whether to sort the tasks first or not
* set a number of extra iterations of greedy alghoritm (in order to get more reliable average runtime measurements)
* generate new test instance files based on the data provided
* display the greedy alghotitm run & results history

### To run:
Go to the application root folder and use:
php Main.php

If you decide to run a genethic alghoritm - you have to stop the program manually. It is an intended choice, as - for the testing purposes - desired runtimes vary and might be quite long. During the testing phase - 5 minutes has proven itself to be enough for most of the default instances to achieve a satisfactory result.

## The genetic alghoritm 
The genetic algorithm is a metaheuristic inspired by natural selection processes. It is used to generate highly optimized solutions based on biological mechanisms - mutations, crossing over and selection.

The process begins with the selection of the 'best' (in our case - with the lowest Tmax) individuals. By crossing them, we get the offspring - inheriting parental traits - to be added to the next population. If the parents were of 'high quality', the offspring should be similar, or better. This process is repeated until we obtain a generation closest to the desired optimum.

The five phases of the genetic algorithm:
1. Establishing of an initial population
2. Comparison of individuals
3. Selection
4. Crossbreeding
5. Mutation

## Pseudocode

- Create an initial population by adding new individuals being a result of several mutations of the starting individual
- Genetic algorithm:
  - execution of the greedy algorithm for each individual from the starting population
  - sorting individuals in ascending order by their Tmax (maximal execution time) results, obtained with the greedy algorithm
  - transfer of the subject with the best time to a new population
  - every 10th generation: cloning and mutation of an individual with the best Tmax and adding it to the new population
  - for the remaining new individual places in the new population:
    - selecting two random 'parents' from the first half (with the best times) of individuals from the previous population
    - selecting two random indexes (points of intersection), needed to perform the crossing
    - crossing selected parents:
      - creating a 'child' array of the 'parent' array length
      - dividing the first parent based on the given indexes (extracting the interval)
      - copying the selected interval to the child's table, while maintaining the indexes of specific task times
      - filling the missing 'child' times with the missing 'parent' times
      - return of a new child, being a result of parents cross-over
  - for each individual in the new population: execution of the greedy algorithm and saving the population as starting one for the next generation

Mutation - swapping the places of tasks with two random indexes:
- when creating a new population: triggered several times, number based on the passed 'mixing' percentage parameter
- with sporadic mutation of the best individual from a given population: triggered only two times