<?php
require './config/config.ini.php';

$appObjFile = $GLOBALS['config']['root_lib'] . 'App.php';

if (is_file($appObjFile)) {
    require $appObjFile;
} else {
    die('App file is not found!');
}

$appObj = new App($GLOBALS['config']['db']);
$appObj->GetPageDetails();
$appObj->GetMenus();

$params = $appObj->getRequestParams('post', '*');
$resultMessage = null;

$quizObj = new QuizActions($appObj);

/**
 * Change current mode can be processed by AJAX too if JavaScript is enabled.
 */
if ((!empty($params['changeMode']) && !empty($params['changeGroup'])) || !empty($params['ajaxSubmit'])) {
    if (!empty($params['ajaxSubmitValue'])) {
        $mode = trim($params['ajaxSubmitValue']);
    } else {
        $mode = $params['changeGroup'];
    }

    $result = $quizObj->setQuizMode($mode);

    $resultMessage = $GLOBALS['config']['quiz']['messages'][(int)$result]['text'];
    $resultClass = $GLOBALS['config']['quiz']['messages'][(int)$result]['class'];

    if (!empty($params['ajaxSubmit'])) {
        $appObj->setAjaxHeaders();

        if (!empty($resultMessage) && !empty($resultClass)) {
             echo json_encode(array('message' => $resultMessage, 'class' => $resultClass));
        }

        /**
         * Close the database connection.
         */
        $dbObj = null;
        return;
    }
}

$quizObj->GetQuotesDetails($params);

if (empty($params)) {
    $appObj->sessObj->correctAnswers = 0;
    $appObj->sessObj->alreadyAnswered = null;
    $appObj->sessObj->shownQuotesId = null;
}

if (!empty($params['startButton'])) {
    $appObj->sessObj->alreadyAnswered = null;
}

if (!empty($params['yesButton']) || !empty($params['noButton'])
        || (!empty($params['submitMultiple']) && !empty($params['author']))) {
    /**
     * If the user try to answer to already answered question,
     * error message will be displayed.
     */
    $answers = $appObj->sessObj->alreadyAnswered;
    $alreadyAnswered = false;
    if (is_array($answers) && array_key_exists($params['quoteId'], $answers)) {
        $alreadyAnswered = true;
    } else {
        $answers[$params['quoteId']] = true;
        $appObj->sessObj->alreadyAnswered = $answers;
    }

    $author = !empty($params['author']) ? $params['author'] : '';

    if (mb_strlen($author) > 5) {
        $checkFlag = true;

        if ($alreadyAnswered === false) {
            if (!empty($params['noButton'])) {
                $checkFlag = false;
            }

            list($result, $correctAuthor) = $quizObj->checkAnswer($author, $checkFlag);

            if ((int)$result == 1) {
                $appObj->sessObj->correctAnswers++;
            }

            $resultMessage = $GLOBALS['config']['messages'][(int)$result]['text'] . $correctAuthor;
            $resultClass = $GLOBALS['config']['messages'][(int)$result]['class'];
        } else {
            $resultMessage = $GLOBALS['config']['messages']['alreadyAnswered']['text'];
            $resultClass = $GLOBALS['config']['messages']['alreadyAnswered']['class'];
        }
    }
}

/**
 * Close the database connection.
 */
$dbObj = null;

$appObj->tplObj->set('resultMessage', Cleaner::stripTags($resultMessage));
$appObj->tplObj->set('resultClass', !empty($resultClass) ? $resultClass : '');

$appObj->tplObj->set('innerTemplate', $appObj->pageDetails['template']);
$appObj->tplObj->set('pageTitle', $appObj->pageDetails['title']);
$appObj->tplObj->set('sessObj', $appObj->sessObj);

$appObj->tplObj->set('http_root_css', $GLOBALS['config']['http_root_css']);
$appObj->tplObj->set('http_root_js', $GLOBALS['config']['http_root_js']);

/**
 * Fetch the main.tpl file where all HTML is printed.
 */
echo $appObj->tplObj->fetch($GLOBALS['config']['root_tpl'] . 'main.tpl');
?>