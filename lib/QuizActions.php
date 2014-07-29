<?php
/**
 * Action related to building a Famous quotes quiz which works in two modes:
 * Binary (Yes/No) - which is the default and with Multiple choice questions.
 * Mode can changed on a separate page. Request is processed by AJAX.
 * However works without Javascript enabled too.
 *
 * The quiz is build based on all quotes which are currently transferred
 * from the web service.
 * Quotes are splitted into a 6 categories stored into a separate table.
 *
 * Current quote id and the random author (for binary mode)
 * or authors (for multiple choice mode)are stored into a hidden fields.
 *
 * User is also able to start the quiz once again after he/she answer to all questions.
 * A result of correct answers will be printed as well.
 *
 * All shown questions are stored into a session together with the total number
 * of correct answers.
 *
 * @TODO: Add custom limit of the questions.
 * @TODO: User to be able to see on which number of question is and how many
 *        are left till the end of the quiz.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class QuizActions
{
    /**
     * Main application Object.
     * @var App
     */
    protected $_appObj = null;

    /**
     * Database PDO Object.
     * @var PDO
     */
    protected $_dbObj = null;

    /**
     * Session Object.
     * @var Session
     */
    protected $_sessObj = null;

    /**
     * Template Object.
     * @var Template
     */
    protected $_tplObj = null;

    /**
     * Quotes table name.
     * NOTE: Could be added into a configuration.
     * @var string
     */
    protected $_quotesTable = 'quotes';

    /**
     * All quotes IDs.
     * @var array
     */
    protected $_quotesIds = array();

    /**
     * Configuration array.
     * @var array
     */
    protected $_quizConfig = array();

    /**
     * Current quiz mode.
     * @var srting
     */
    protected $_quizMode = '';

    /**
     * Current quote crrect auhtor.
     * @var srting
     */
    protected $_correctAuthor = '';


    /**
     * Store Objects into internal properties.
     * Get configuration.
     * @param PDO $dbObj
     * @param Template $tplObj
     * @return void
     */
    public function __construct(&$appObj)
    {
        $this->_appObj = $appObj;
        $this->_dbObj = $this->_appObj->dbObj;
        $this->_sessObj = $this->_appObj->sessObj;
        $this->_tplObj = $this->_appObj->tplObj;

        $this->_SetConfig();
    }

    /**
     * Set configuration which is stored into the main config file.
     * @throws Exception
     * @return void
     */
    protected function _SetConfig()
    {
        if (!empty($GLOBALS['config']['quiz'])) {
            $this->_quizConfig = $GLOBALS['config']['quiz'];

            if (mb_strlen($this->_sessObj->quizMode) > 3) {
                $this->_quizMode = $this->_sessObj->quizMode;
            } else {
                $this->_quizMode = $this->_sessObj->quizMode = $this->_quizConfig['default_quiz_mode'];
            }

            $this->_SetQuizModeTemplate();
        } else {
            throw new Exception('Missing quiz configuration data!');
        }
    }

    /**
     * Set current quiz mode.
     * If the selected mode is the same as the current one, then false is returned.
     * @param string $quizMode
     * @return boolean
     */
    public function setQuizMode($quizMode)
    {
        if (!empty($quizMode) && mb_strlen($quizMode) > 3 && in_array($quizMode, $this->_quizConfig['allowed'])) {

            if ($quizMode == $this->_quizMode) {
                return false;
            }

            $this->_quizMode = $this->_sessObj->quizMode = trim($quizMode);
            $this->_SetQuizModeTemplate();
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get current quiz mode.
     * @return string
     */
    public function getQuizMode()
    {
        return $this->_quizMode;
    }

    /**
     * Store current quiz mode template into the session.
     * @return void
     */
    protected function _SetQuizModeTemplate()
    {
        $templateFile = trim($this->_quizConfig['templates'][$this->_quizMode]);

        if (is_file($GLOBALS['config']['root_tpl'] . $templateFile)) {
            $this->_sessObj->quizTemplatePath = $GLOBALS['config']['root_tpl'] . $templateFile;
        }
    }

    /**
     * Get quotes details.
     * @param array $params
     * @return void
     */
    public function GetQuotesDetails($params = array())
    {
        if (!empty($params['startButton'])) {
            $this->_sessObj->shownQuotesId = null;
            $this->_sessObj->correctAnswers = 0;
        }

        $this->_getQuotesIds();
        $quotesIds = $this->_quotesIds;
        /**
         * Get the first fetched quote.
         */
        $id = array_shift($quotesIds);

        $thanksMsg = null;

        if (is_array($this->_sessObj->shownQuotesId)) {
            $lastShownId = end($this->_sessObj->shownQuotesId);

            if (!empty($params['nextButton'])) {
                /**
                 * If the user refresh the page on after already clicked Next button,
                 * there is additional check whether the answer is already given.
                 * Prevent going on next question before answering on the previous.
                 */
                if (is_array($this->_sessObj->alreadyAnswered) && array_key_exists($lastShownId, $this->_sessObj->alreadyAnswered)) {
                    $quotesIds = array_diff($quotesIds, $this->_sessObj->shownQuotesId);
                    $id = array_shift($quotesIds);
                } else {
                    $id = $lastShownId;
                }
            } else {
                $id = $lastShownId;
            }

            if (empty($id)) {
                $thanksMsg = $GLOBALS['config']['messages']['thanks']['text'];
                $thanksMsg = str_replace('{correctAnswers}', $this->_sessObj->correctAnswers, $thanksMsg);
                $this->_tplObj->set('thanksMsg', Cleaner::stripTags($thanksMsg));
            }
        }

        if (!empty($params['nextButton']) || is_null($this->_sessObj->shownQuotesId)) {
            $arrayAdd = $this->_sessObj->shownQuotesId;
            $arrayAdd[] = $id;
            $this->_sessObj->shownQuotesId = $arrayAdd;
        }

        $quote = $this->_getQuoteById($id);
        $this->_sessObj->selectedQuoteId = $quote['id'];

        if ($this->_quizMode == 'binary') {
            $randAuthors = (array)$this->_getRandomAuthor();
        } else {
            $this->_correctAuthor = $this->_getAuthorByQuoteId($quote['id']);
            $randAuthors = $this->_getRandAuthorsExceptCorrect(2);

            if (is_array($randAuthors)) {
                $randAuthors = array_merge($randAuthors, (array)$this->_correctAuthor);
                shuffle($randAuthors); //randomize the order of the authors :)
            }
        }

        /**
         * Already genertaed authors are stored into the session.
         * Prevent user to be able to refresh the page in order to see which
         * author name persist in each result and quess it easily.
         */
        $randSession = $this->_sessObj->randAuthors;

        if (is_array($randSession)) {
            if (array_key_exists($this->_quizMode, $randSession)) {
                $randSession = $randSession[$this->_quizMode];
            }
        }

        if (is_array($randSession) && array_key_exists($id, $randSession) && is_array($randSession[$id])) {
            $randAuthorsArr = $randSession[$id];
        } else {
            $randAuthorsArr = $this->_sessObj->randAuthors = array($this->_quizMode => array($id => $randAuthors));
            $randAuthorsArr = $randAuthorsArr[$this->_quizMode][$id];
        }

        $this->_tplObj->set('quoteText', Cleaner::stripTags($quote['quote']));
        $this->_tplObj->set('quoteId', (int)$quote['id']);
        $this->_tplObj->set('authorsAnswers', Cleaner::stripTags($randAuthorsArr));
    }

    /**
     * Get all quotes IDs stored into the database.
     * @return array|null
     */
    protected function _getQuotesIds()
    {
        $sth = $this->_dbObj->prepare('SELECT id FROM ' . $this->_quotesTable . ' ORDER BY id')->execute();
        $ids = $sth->fetchAllAssoc();

        $idsAll = array();
        foreach ($ids as $id) {
            $idsAll[] = $id['id'];
        }

        $this->_quotesIds = $idsAll;

        if (empty($this->_quotesIds)) {
            return null;
        }
    }

    /**
     * Get quote by a given id.
     * @param integer $id
     * @return array|null
     */
    protected function _getQuoteById($id)
    {
        if ($id > 0) {
            $sth = $this->_dbObj->prepare('SELECT id, quote FROM ' . $this->_quotesTable . ' WHERE id = ? LIMIT 1', array((int)$id))->execute();
            $ids = $sth->fetchRowAssoc();

            if (!empty($ids)) {
                return $ids;
            }
        }

        return null;
    }

    /**
     * Get quote by a given id.
     * @param integer $id
     * @return array|null
     */
    protected function _getAuthorByQuoteId($id)
    {
        if ($id > 0) {
            $sth = $this->_dbObj->prepare('SELECT author FROM ' . $this->_quotesTable . ' WHERE id = ? LIMIT 1', array((int)$id))->execute();
            $author = $sth->fetchRowAssoc();

            if (!empty($author['author'])) {
                return $author['author'];
            } else {
                return null;
            }
        }
    }

    /**
     * Get random author from all stored into the database.
     * @return string
     */
    protected function _getRandomAuthor()
    {
        $sth = $this->_dbObj->prepare('SELECT author FROM ' . $this->_quotesTable)->execute();
        $authors = $sth->fetchAllAssoc();

        $randomKey = array_rand($authors);

        return $authors[$randomKey]['author'];
    }

    /**
     * Get a number of random authors apart from the correct one.
     * Used for multiple choice mode.
     * @param integer $count [optional]
     * @return array
     */
    protected function _getRandAuthorsExceptCorrect($count = 1)
    {
        if (!empty($this->_correctAuthor)) {
            $sth = $this->_dbObj->prepare('SELECT author FROM ' . $this->_quotesTable . ' WHERE author NOT LIKE (?)', array($this->_correctAuthor))->execute();
            $authors = $sth->fetchAllAssoc();

            $randAuthors = array();
            if (is_array($authors) && !empty($authors) && $count > 0) {
                do {
                    $randomKey = array_rand($authors);

                    if (!in_array($authors[$randomKey]['author'], $randAuthors)) {
                        $randAuthors[] = $authors[$randomKey]['author'];
                    }
                } while (count($randAuthors) < $count);
            }

            return $randAuthors;
        }
    }

    /**
     * Check whether the answer is correct based on given author and check flag.
     * If check flag is false, then it will be checked for not correct and vice versa.
     * @param string $author
     * @param boolean $checkFlag
     * @return boolean|null
     */
    public function checkAnswer($author, $checkFlag)
    {
        if ($this->_sessObj->selectedQuoteId > 0 && !empty($author)) {
            $sth = $this->_dbObj->prepare('SELECT author FROM ' . $this->_quotesTable . ' WHERE id = ?', array((int) $this->_sessObj->selectedQuoteId))->execute();
            $authorChecked = $sth->fetchRowAssoc();

            if (($authorChecked['author'] == trim($author) && $checkFlag === true) || ($authorChecked['author'] != trim($author) && $checkFlag === false)) {
                $result = true;
            } else {
                $result = false;
            }

            return array($result, $authorChecked['author']);
        } else {
            return null;
        }
    }
}
?>