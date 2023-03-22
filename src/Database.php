<?php

declare(strict_types = 1); // turn on the strong typing


namespace Visma\dbClass;

class Database
{
    //👇 This is called the DocBlock style commenting

    /**
     * The PDO object.
     *
     * @var \PDO  //var - Specifies the type of property
     */
    private $pdo;

    /**
     * Status of connection to db.
     *
     * @var bool
     */
    private $isConnected;

    /**
     * PDO statement object.
     *
     * @var \PDOStatement
     */
    private $statement;

    /**
     * The database settings.
     *
     * @var array
     */
    protected $settings = [];

    /**
     * The parameters of the SQL query.
     *
     * @var array
     */
    private $parameters = [];



    /**
     * Database constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        //overwrite the incoming data to the protected property.
        $this->settings = $settings;

        //and connect - That's all what our constructor must do.
        $this->connection();
    }

    private function connection()
    {
        //(DSN - Data source name) - frequent practice in naming such variables
        $dsn = 'mysql:dbname=' . $this->settings['dbname'] . ';host=' . $this->settings['host'];
        try {
             $this->pdo = new \PDO($dsn, $this->settings['user'], $this->settings['password'], [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->settings['charset']]);

             $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // enabled error reporting mode
             $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // turned off the emulation of prepared queries

             // everything is fine, notify the variable about it
             $this->isConnected = true;

        // if not, throw an exception
        }catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function closeConnection()
    {
        // to close connection, we need just assign null for property pdo
        $this->pdo = null;
    }


    /**
     * Query Initialization
     *
     * @param string $query
     * @param array $parameters
     * @return void
     */
    private function init(string $query, array $parameters = []) //we can set the data types for the method parameters
    {
        // check the property for connection
        if (!$this->isConnected) {
            $this->connection();
        }

        try {
            //Preparing the query
            $this->statement = $this->pdo->prepare($query);

            //Binding parameters
            $this->bind($parameters);

            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param => $value) {

                    //setting data types for our params
                    if (is_int($value[1])) {
                        $type = \PDO::PARAM_INT;
                    }elseif (is_bool($value[1])) {
                        $type = \PDO::PARAM_BOOL;
                    }elseif (is_string($value[1])) {
                        $type = \PDO::PARAM_STR;
                    }else {
                        $type = \PDO::PARAM_NULL;
                    }
                    $this->statement->bindValue($value[0], $value[1], $type);

                }
            }
            //execute the query
            $this->statement->execute();

        }catch (\PDOException $e) {
            exit($e->getMessage());
        }

        // cleaning the parameters property after execution
        $this->parameters = [];
    }


    /**
     * Binding params
     *
     * @param array $parameters
     * @return void // void indicates that the method returns nothing
     */
    private function bind(array $parameters): void //we can also denote the return type, after the parameters
    {
        if (!empty($parameters) and is_array($parameters)){
            $columns = array_keys($parameters);

            foreach ($columns as $i => &$column) {
                $this->parameters[sizeof($this->parameters)] = [
                    ':' . $column,
                    $parameters[$column]
                ];
            }
        }
    }


    /**
     * Clearing query and validating output.
     *
     * @param string $query
     * @param array $parameters
     * @param $mode
     * @return array|false|int|null
     */
    public function query(string $query, array $parameters = [], $mode = \PDO::FETCH_ASSOC)
    {
        //removing spaces and hyphenations, in case there are fans of writing queries in this way
        $query = trim(str_replace('\r', '', $query));

        //initializing the query
        $this->init($query, $parameters);

        //cleaning the escaped characters by regular expression
        $rawStatement = explode(' ', preg_replace("/\s+|\t+|\n+/", " ", $query));

        //select the first word from the query and convert it to lowercase
        $statement = strtoLower($rawStatement[0]);

        if (in_array($statement, ['select', 'show'], true)) {
            return $this->statement->fetchAll($mode);
        }elseif (in_array($statement, ['insert', 'update', 'delete'], true)) {
            return $this->statement->rowCount();
        }else {
            return null;
        }
    }

    /**
     * @return false|string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }


}