<?php
/**
 * Database actions.
 * @author Nelly Todorova <nelly.todorova@yahoo.com>
 */
class DataBase
{
    /**
     * Database PDO Object.
     * @var PDO
     */
    private $_dbObj = null;

    /**
     * Database database handle.
     * @var PDO
     */
    private $_sth = null;

    /**
     * Parameters used into queries.
     * @var array
     */
    private $_params = array();

    /**
     * Generated SQL query.
     * @var string
     */
    private $_sql = '';


    /**
     * Connect to a PostgreSQL database.
     * @param array $config
     * @return Database PDO Object
     * @throws Exception
     */
    public function __construct($config)
    {
        if (is_array($config) && !empty($config)) {
            try {
                $this->_dbObj = new PDO('pgsql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'] . ';user=' . $config['user'] . ';password=' . $config['password']);
                $this->_dbObj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->_dbObj;
            } catch (PDOException $e) {
                echo $e->getMessage();
                die();
            }
        } else {
            throw new Exception('No relevant DB configuration set!');
        }
    }

    /**
     * Prepare SQL query.
     * @param string $sql
     * @param parameters $params [optional]
     * @param array $pdoOptions [optional]
     * @return Database PDO Object
     */
    public function prepare($sql, $params = array(), $pdoOptions = array())
    {
        $this->_sth = $this->_dbObj->prepare($sql, $pdoOptions);
        $this->_params = $params;
        $this->_sql = $sql;
        return $this;
    }

    /**
     * Execute SQL query.
     * @param array $params [optional]
     * @return Database PDO Object
     */
    public function execute($params = array())
    {
        if (!empty($params)) {
            $this->_params = $params;
        }
        $this->_sth->execute($this->_params);
        return $this;
    }

    public function fetchAllAssoc()
    {
        return $this->_sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchRowAssoc()
    {
        return $this->_sth->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchAllNum()
    {
        return $this->_sth->fetchAll(PDO::FETCH_NUM);
    }

    public function fetchRowNum()
    {
        return $this->_sth->fetch(PDO::FETCH_NUM);
    }

    public function fetchAllObj()
    {
        return $this->_sth->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchRowObj()
    {
        return $this->_sth->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAllColumn($column)
    {
        return $this->_sth->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    public function fetchRowColumn()
    {
        return $this->_sth->fetchColumn();
    }

    public function fetchAllClass($class)
    {
        return $this->_sth->fetchAll(PDO::FETCH_CLASS, $class);
    }

    public function fetchRowClass($class)
    {
        return $this->_sth->fetch(PDO::FETCH_BOUND, $class);
    }

    public function getLastInsertId()
    {
        return $this->_dbObj->lastInsertId();
    }

    public function getAffectedRows()
    {
        return $this->_sth->rowCount();
    }

    public function getSTH()
    {
        return $this->_sth;
    }
}
?>