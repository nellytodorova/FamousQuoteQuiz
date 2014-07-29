<?php
/**
 * Main actions related to building an application.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class App
{
    /**
     * Template Object.
     * @var Template
     */
    public $tplObj = null;

    /**
     * Database PDO Object.
     * @var PDO
     */
    public $dbObj = null;

    /**
     * Session Object.
     * @var Session
     */
    public $sessObj = null;

    /**
     * All page details.
     * @var array
     */
    public $pageDetails = array();

    /**
     * Pages table name.
     * NOTE: Could be added into a configuration.
     * @var string
     */
    protected $_pagesTable = 'content.pages';

    /**
     * Menu groups table name.
     * NOTE: Could be added into a configuration.
     * @var string
     */
    protected $_menuGroupsTable = 'content.menu_groups';

    /**
     * Menu links table name.
     * NOTE: Could be added into a configuration.
     * @var string
     */
    protected $_menuLinksTable = 'content.menu_links';


    /**
     * Perform needed initial actions as set error handler function,
     * set autoload and initialize relevant objects.
     * @param array $dbConfig
     * @return void
     */
    public function __construct($dbConfig)
    {
        set_exception_handler(array($this, 'exceptionHandler'));
        $loaderFile = $GLOBALS['config']['root_lib'] . 'Loader.php';

        if (is_file($loaderFile)) {
            include_once $loaderFile;
            Loader::RegisterAutoLoad();
        }

        $this->tplObj = new Template();

        if (is_array($dbConfig) && !empty($dbConfig)) {
            $this->dbObj = new DataBase($dbConfig);
        }

        $this->sessObj = new Session('_sess');
    }

    /**
     * Get current page details and store them into a session.
     * @return void
     */
    public function GetPageDetails()
    {
        $script = explode('/', $_SERVER['SCRIPT_NAME']);
        $page = urlencode(end($script));

        $sth = $this->dbObj->prepare('SELECT id, uri, title, template FROM ' . $this->_pagesTable . ' WHERE uri = ? LIMIT 1');
        $stmt = $this->dbObj->getSTH();
        $stmt->bindParam(1, $page, PDO::PARAM_STR);
        $stmt->execute();
        $this->pageDetails = $sth->fetchRowAssoc();

        if (empty($this->pageDetails)) {
            /**
             * Redirect to a default page, if none is set.
             */
            $sth = $this->dbObj->prepare('SELECT uri FROM ' . $this->_pagesTable . ' WHERE is_default = ? ORDER BY id LIMIT 1', array(1))->execute();
            $defPage = $sth->fetchRowAssoc();
            header('Location:' . $defPage['uri']);
        }

        $this->sessObj->pageDetails = $this->pageDetails;
    }

    /**
     * Build array for all menu links which can be printed into a template.
     * @return void
     */
    public function GetMenus()
    {
        $sth = $this->dbObj->prepare('SELECT ml.id, ml.menu_group_id, mg.name AS menu_group, mg.template, p.uri, p.title
                                      FROM ' . $this->_menuLinksTable . ' AS ml
                                      JOIN ' . $this->_menuGroupsTable . ' AS mg
                                      ON ml.menu_group_id = mg.id
                                      JOIN ' . $this->_pagesTable . ' AS p
                                      ON ml.page_id = p.id')->execute();

        $menus = $sth->fetchAllAssoc();

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $menuGroups[$menu['menu_group']]['template'] = $menu['template'];
                $menuGroups[$menu['menu_group']]['links'][$menu['id']] = array(
                        'uri' => $menu['uri'],
                        'title' => $menu['title']
                );
            }

            foreach ($menuGroups as $group => $links) {
                if (is_file($GLOBALS['config']['root_tpl'] . $links['template'])) {
                    $menuLinks = $links['links'];
                    $this->tplObj->set($group . 'LinksArr', $menuLinks);
                    $template = $this->tplObj->fetch($GLOBALS['config']['root_tpl'] . $links['template']);

                    $this->tplObj->set($group, $template);
                }
            }
        }
    }

    /**
     * Custom exception handler function.
     * Separate configuration is stored into configError.ini.php.
     * Log files are stored into error_log folder.
     * At this stage is basic. Could be extended.
     * For example, email to the web support team can be
     * sent with error details.
     *
     * @param Exception $ex
     * @return void
     */
    public function exceptionHandler(Exception $ex)
    {
        $configFile = './config/configError.ini.php';

        if (is_file($configFile)) {
            include $configFile;
        }

        $errCode = $ex->getCode();
        $errorMsg = $GLOBALS['configErr']['errorCodes'][$errCode];
        $errorMsg .= $ex;

        $errorLog = $GLOBALS['config']['root_error_log'] . 'errors.log';
        error_log($errorMsg . ' ' . $errCode, 3, $errorLog);

        if ($GLOBALS['configErr']['errorsDisplay'] === true) {
            echo '<pre>' . print_r($errorMsg, true) . '</pre>';
        } else {
            if (empty($errCode)) {
                $errCode = 500;
            }

            header(urlencode($_SERVER['SERVER_PROTOCOL'] . ' ' . $errCode . ' ' . $errorMsg), true, $errCode);
            die();
        }
    }

    /**
     * Fetch request params.
     * @param string $paramType
     * @param string $param [optional] - if '*', then all values will be returned
     * @return array
     */
    public function getRequestParams($paramType, $param = '*')
    {
        if (!empty($paramType) && mb_strlen($paramType) > 2) {
            switch($paramType) {
                case 'get':
                    if ($param == '*') {
                        return $_GET;
                    } else if (!empty($_GET[$param])) {
                        return $_GET[$param];
                    }
                    break;

                case 'post':
                    if ($param == '*') {
                        return $_POST;
                    } else if (!empty($_POST[$param])) {
                        return $_POST[$param];
                    }
                    break;

                default:
                    break;
            }
        }

        return null;
    }

    /**
     * Set AJAX headers.
     * @return void
     */
    public function setAjaxHeaders()
    {
        header('Content-Type: application/json');
        header('Expires: 0');
        header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
    }

    /**
     * Store message text into a log file.
     * Currently used only for the quotes transfer.
     * @param string $fileName
     * @param string $logMsg
     * @return boolean
     */
    public function setLog($fileName, $logMsg = '')
    {
        $logPath = $GLOBALS['config']['root_jobs_log'] . DIRECTORY_SEPARATOR . $fileName;

        if (is_file($logPath)) {
            if (empty($logMsg)) {
                /**
                 * The modification date of the file will be changed though.
                 */
                touch($logPath);
            }

            $log = file_put_contents($logPath, $logMsg, FILE_APPEND);
        }

        return ($log !== false) ? true : false;
    }
}
?>