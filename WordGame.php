<?php

class WordGame
{
    /**
     * The filename of the file containing all of the valid words
     */
    private const WORDS_FILE = 'wordlist.txt';

    /**
     * The length of base string used at the beginning of each new game
     */
    private const BASE_STRING_LENGTH = 10;

    /**
     * The number of high scores that are allowed to be kept
     */
    private const HIGH_SCORES_ALLOWED_LENGTH = 10;

    /**
     * An array containing all of the valid words
     */
    private $words;

    /**
     * An associative array containing the highscoring words and their associated score
     */
    private $highScores = [];

    /**
     * A base string generated at the beginning of a new game
     */
    private $baseString;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        // On creation of a new WordGame class, load the contents of wordlist.txt into an array for searching
        $this->loadWordListOrFail();

        // Explicitly sort the array alphabetically just incase the file is not sorted
        sort($this->words);

        // Set the base string in order to begin the game
        $this->setBaseString();

        // Prompt for a guess
        $this->promptForGuess();
    }

    /*
    Submit a word on behalf of a player. A word is accepted if its letters are
    contained in the base string used to construct the game AND if it is in the
    word list provided: wordlist.txt.

    If the word is accepted and its score is high enough, the submission should
    be added to the high score list. If there are multiple submissions with the
    same score, all are accepted, BUT the first submission with that score
    should rank higher.

    A word can only appear ONCE in the high score list. If the word is already
    present in the high score list the submission should be rejected.

    @parameter word. The player's submission to the game. All submissions may
    be assumed to be lowercase and contain no whitespace or special characters.

    @returns the score for the submitted word if the submission is accepted. And 0 otherwise.
    */
    public function submitWord($word)
    {
        // Exit the game if the input is 'quit!' is entered
        if ($word == 'quit!') {
            echo "\n\n✋ Good bye Thank you for playing!\n\n";
            exit();
        }

        echo "\n\n⌛ Checking your answer '" . $word . "' ...\n\n";

        if (empty($word)) {
            echo "\n\n🙄 Looks like you submitted too early - try again?\n\n";
            return 0;
        } elseif ($this->checkValidity($word)) {
            $points = strlen($word);
            echo "\n\n🎉 Good Guess! '" . $word . "' scores you " . $points . " points!\n\n";
            return $points;
        } else {
            echo "\n\n😒 Unlucky - '" . $word . "' is not a valid word!\n\n";
            return 0;
        }
    }

    /*
    Return word entry at given position in the high score list, position 0 being the
    highest (best score) and position 9 the lowest. You may assume that this method will
    never be called with position > 9.

    @parameter position Index position in high score list

    @return the word entry at the given position in the high score list, or null
    if there is no entry at the position requested
    */
    public function getWordEntryAtPosition($position)
    {
        // Make an array of the words(keys) from the high scores
        $highScoringWords = array_keys($this->highScores);

        if (!empty($highScoringWords[$position])) {
            return $highScoringWords[$position];
        } else {
            return null;
        }
    }

    /*
    Return the score at the given position in the high score list, position 0 being the
    highest (best score) and position 9 the lowest. You may assume that this method will
    never be called with position > 9.

    What is your favourite colour? Please put your answer in your submission
    (this is for testing if you have read the comments).

    @parameter position Index position in high score list

    @return the score at the given position in the high score list, or null if
    there is no entry at the position requested
    */
    public function getScoreAtPosition($position)
    {
        // Make an array of the points(values) from the high scores
        $highScoringPoints = array_values($this->highScores);

        if (!empty($highScoringPoints[$position])) {
            return $highScoringPoints[$position];
        } else {
            return null;
        }
    }
    
    /**
     * Load the contents of wordlist.txt or throw error
     */
    private function loadWordListOrFail()
    {
        $filePath = self::WORDS_FILE;
        
        if (!file_exists($filePath)) {
            throw new Exception('The file '. $filePath. ' does not exist');
            exit;
        }

        $size = filesize($filePath);

        if (empty($size)) {
            throw new Exception('The wordslist.txt file is empty.');
            exit;
        }
        
        $this->words = file($filePath, FILE_IGNORE_NEW_LINES);
    }

    /**
     * Create a random string whose length is determined by the BASE_STRING_LENGTH constant
     * This solution allows for repeatable letters
     */
    private function setBaseString()
    {
        // $this->baseString = 'areallylongword';
        $this->baseString = substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 100)), 0, self::BASE_STRING_LENGTH);

        echo "\n\nThe base string is: \n\n";
        echo "    $this->baseString    ";
        echo "\n\n";
    }

    /**
     * Prompt for a guess
     */
    private function promptForGuess()
    {
        echo "\n\nPlease enter your guess: \n\n";

        $stdin = fopen('php://stdin', 'r');

        $input = strtolower(trim(fgets($stdin)));

        $points = $this->submitWord($input);

        if (!empty($points)) {
            $this->checkHighScores($input, $points);
        }

        // Ask for a new guess
        $this->promptForGuess();
    }

    /**
     * Check validity of the current guess
     */
    private function checkValidity($word)
    {
        if ($this->allLettersInBaseString($word) && $this->isValidWord($word) && $this->checkLetterUsage($word)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if all characters of the word are contained in the base string
     */
    private function allLettersInBaseString($word)
    {
        $letters = str_split($word);
        $valid = true;

        foreach ($letters as $letter) {
            if (strpos($this->baseString, $letter) !== false) {
                continue;
            } else {
                // Letter not found in the base string
                $valid = false;
                return $valid;
            }
        }

        return $valid;
    }

    /**
     * Check if the word exists in the array of valid words
     */
    private function isValidWord($word)
    {
        return in_array($word, $this->words);
    }

    /**
     * Check letter usage
     */
    private function checkLetterUsage($word)
    {
        $valid = true;

        foreach (array_count_values(str_split($word)) as $char => $count) {
            // Iterate each unique letter in word
            if ($count > substr_count($this->baseString, $char)) {
                // Compare current char's count vs same char's count in baseString
                $valid = false;
                return $valid;
                break;
            }
        }

        return $valid;
    }

    /**
     * Check if the word can be added to the high scores
     */
    private function checkHighScores($word, $points)
    {
        if (array_key_exists($word, $this->highScores)) {
            echo "\n\n😒 Unlucky, " . $word . " is already in our list of high scores so it can't be added this time!\n\n";
        } else {
            echo "\n\n🎉 Congratulations - " . $word . " is a unique answer! Your guess has been added to our list of high scores!!!!\n\n";

            // Add entry to high scores
            $this->highScores[$word] = $points;

            // Sort the high scores by number of points in reverse order
            arsort($this->highScores);

            // Keep only the desired amount of high scores defined by HIGH_SCORES_ALLOWED_LENGTH
            $this->highScores = array_slice($this->highScores, 0, self::HIGH_SCORES_ALLOWED_LENGTH - 1);

            /*
            If the score for this word is equal to the lowest score in the high scores, then it can be kept
            I haven't thought this fully through actually as there could be other situations where this applies
            and not just at the lower end of the scores.
            */
            if ($points === $this->getScoreAtPosition(-1)) {
                // Add entry to high scores (again)
                $this->highScores[$word] = $points;
            }

            $index = count($this->highScores) - 1;
            echo "\n🤙 High Scores Count: " . count($this->highScores) . "\n";
            echo "\n🤙 Word Entry: " . $this->getWordEntryAtPosition($index) . "\n";
            echo "\n🤙 Points Scored: " . $this->getScoreAtPosition($index) . "\n";
        }
    }
}

$wordGame = new WordGame;
