<?php
global $config;

/**
 * All paths settings using the global variable $config.
 * @var array
 */
$config['folder'] = '/FamousQuoteQuiz/';
$config['http_root'] = 'http://' . $_SERVER['HTTP_HOST'] . $config['folder'];
$config['http_root_tpl'] = $config['http_root'] . 'tpl/';
$config['http_root_css'] = $config['http_root_tpl'] . 'css/';
$config['http_root_js'] = $config['http_root_tpl'] . 'js/';

$config['root'] = $_SERVER['DOCUMENT_ROOT'] . $config['folder'];
$config['root_jobs'] = $config['root'] . 'jobs/';
$config['root_jobs_log'] = $config['root_jobs'] . 'log/';
$config['root_error_log'] = $config['root'] . 'error_log/';
$config['root_lib'] = $config['root'] . 'lib/';
$config['root_tpl'] = $config['root'] . 'tpl/';

/**
 * Transfer configuration settings.
 * @var array
 */
$config['transfer'] = array(
    'db' => array(
        'host' => 'localhost',
        'port' => '5432',
        'database' => 'quiz',
        'user' => 'transfer',
        'password' => '12345',
    ),
    'url' => 'http://api.theysaidso.com/qod?category=',
    'response' => 'json',
    'messages' => array(
        'success' => ' quote(s) were transferred successfully.',
        'fail' => 'No quotes were inserted!',
    ),
    'log' => 'transfer-quotes-',
);

/**
 * Database configuration settings.
 * NOTE: Separate web_user is used for accessing the quiz apart
 * from transfer user who has modify permission of the quotes.
 * @var array
 */
$config['db']['host'] = 'localhost';
$config['db']['port'] = '5432';
$config['db']['database'] = 'quiz';
$config['db']['user'] = 'web_user';
$config['db']['password'] = '12345';

/**
 * Result messages settings.
 * @var array
 */
$config['messages'] = array(
    1 => array(
        'text' => 'Correct! The right answer is: ',
        'class' => 'success',
    ),
    0 => array(
        'text' => 'Sorry, you are wrong! The right answer is: ',
        'class' => 'error',
    ),
    'thanks' => array(
        'text' => 'Thank you for your answers! You have {correctAnswers} correct answer(s).',
    ),
    'alreadyAnswered' => array(
        'text' => 'You already answered to this question!',
        'class' => 'error',
    ),
);

/**
 * Configuration of the quiz.
 * @var array
 */
$config['quiz'] = array(
    'default_quiz_mode' => 'binary',
    'allowed' => array(
        'binary',
        'multiple',
    ),
    'templates' => array(
        'binary' => 'quiz-binary.tpl',
        'multiple' => 'quiz-multiple.tpl',
    ),
    'messages' => array(
        1 => array(
            'text' => 'The mode has been changed succesfully.',
            'class' => 'success',
        ),
        0 => array(
            'text' => 'This is the current mode!',
            'class' => 'error',
        ),
    )
);
?>