<?php
/**
 * Script used for transferring quotes via API into a database.
 * All configuration settings are set into a global array.
 * @see /config/config.ini.php.
 *
 * Parsed type results are JSON and XML. However other types can be easily added.
 *
 * Result of the number of transferred quotes is stored into a log file.
 *
 * Cron Job can be set to trigger the script every day at a certain time as
 * "Quotes of Day" are changed only once per a day. It depends on the used service.
 *
 * NOTE: Quote of the day of each category is only one.
 *
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
require '/../config/config.ini.php';

if (!empty($GLOBALS['config']['transfer'])) {
    $transferConf = $GLOBALS['config']['transfer'];
} else {
    throw new Exception('Custom configuration is not set!');
}

$appObjFile = $GLOBALS['config']['root_lib'] . 'App.php';

if (is_file($appObjFile)) {
    require $appObjFile;
} else {
    die('App file is not found!');
}

$appObj = new App($transferConf['db']);

/**
 * Maximum requests per day.
 * Usually API requests are restricted. It depends on the supplier.
 *
 * IMPORTANT: If maximum requests are hundreds it will be good to add usleep(1000000)
 * for 1 second (or less) in order not to overload the server.
 * @var integer
 */
$maxRequestsPerDay = 10;

/**
 * Quotes table name.
 * @var string
 */
$quotesTable = 'quotes';

/**
 * Quotes categories table name.
 * @var string
 */
$quotesCatsTable = 'quotes_categories';

/**
 * Get all available categories which are stored into a separate table.
 */
$sth = $appObj->dbObj->prepare('SELECT id, name FROM ' . $quotesCatsTable . ' ORDER BY id LIMIT ?');
$stmt = $appObj->dbObj->getSTH();
$stmt->bindParam(1, $maxRequestsPerDay, PDO::PARAM_INT);
$stmt->execute();
$categories = $sth->fetchAllAssoc();

if (is_array($categories) && !empty($categories)) {
    $countInserted = 0;
    $responseType = $transferConf['response'];

    if (empty($responseType)) {
        $responseType = 'json';
    }

    foreach ($categories as $id => $cats) {
        $data = array($cats['id']);

        $url = $transferConf['url'] . $cats['name'];
        $result = file_get_contents($url);

        if ($result !== false) {
            switch ($responseType) {
                case 'json':
                    $parseObj = json_decode($result);
                    break;

                case 'xml':
                    $parseObj = new SimpleXMLElement($result);
                    break;
            }

            if (is_object($parseObj)) {
                $quoteId = trim($parseObj->contents->id);
                $quoteText = trim($parseObj->contents->quote);
                $quoteAuthor = trim($parseObj->contents->author);

                if (empty($quoteId) || empty($quoteText) || empty($quoteAuthor)) {
                    continue;
                }

                /**
                 * Insert quote only if there is no such already added
                 * with the current author against the current category.
                 */
                $sth = $appObj->dbObj->prepare('INSERT INTO ' . $quotesTable . ' (author, quote, category_id, api_id)
                                                SELECT ?, ?, ?, ?
                                                WHERE NOT EXISTS
                                                (SELECT 1 FROM ' . $quotesTable . ' WHERE author = ? AND quote = ? AND category_id = ?)
                                                RETURNING id');
                $stmt = $appObj->dbObj->getSTH();
                $stmt->bindParam(1, $quoteAuthor, PDO::PARAM_STR);
                $stmt->bindParam(2, $quoteText, PDO::PARAM_STR);
                $stmt->bindParam(3, $cats['id'], PDO::PARAM_STR);
                $stmt->bindParam(4, $quoteId, PDO::PARAM_STR);
                $stmt->bindParam(5, $quoteAuthor, PDO::PARAM_STR);
                $stmt->bindParam(6, $quoteText, PDO::PARAM_STR);
                $stmt->bindParam(7, $cats['id'], PDO::PARAM_STR);
                $stmt->execute();
                $insertedId = $sth->fetchRowColumn();

                if (!is_null($insertedId) && $insertedId !== false) {
                    $countInserted++;
                }
            }
        }
    }

    if ($countInserted > 0) {
        $logMsg = $countInserted . $transferConf['messages']['success'];
    } else {
        $logMsg = $transferConf['messages']['fail'];
    }

    $fileName = $transferConf['log'] . date('Y') . '.log';
    $logMsg = date('Y-m-d H:i:s') . ' ----- ' . $logMsg . "\n";
    echo $logMsg;

    $appObj->setLog($fileName, $logMsg);
}

/**
 * Close the database connection.
 */
$dbObj = null;
?>