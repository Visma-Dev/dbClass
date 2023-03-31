# dbClass
The library implements additional validation of values in order to make an already good and safe product even better and even safer.

 ```php
/**
 * Query Initialization
 *
 * @param string $query
 * @param array $parameters
 * @return void
 */
private function init(string $query, array $parameters = [])
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
```


## While working on this project, I gained new practical (as well as theoretical) knowledge in working with:

- #### pdo;
- #### composer;
- #### packagist;
- #### strict typing;
- #### commenting (DocBlock);
- #### regex;


## How to use:



1. Use this command to install the package
 ```shell
  composer require visma-dev/db-class:@dev
  ```
  
2. Connect the autoload.php and import the class 
```php
  require_once __DIR__ . '/vendor/autoload.php';
  
  use Visma\dbClass\Database;
```
  
## Usage examples

### Select

 ```php
  <?php
  
  $db = new Database([
     'host' => 'localhost',
     'dbname' => 'test',
     'user' => 'root',
     'password' => 'root',
     'charset' => 'utf8'
  ]);
  
  $query = $db->query('SELECT * FROM posts ORDER BY id DESC');
  
  $db->closeConnection();
  
```
  
  ### Insert
  
 ```php
  <?php

  $insert = $db->query("INSERT INTO posts (title, content) VALUES (:title, :content)", [
      'title' => 'Hello World',
      'content' => 'My name is dbClass!'
  ]);
```
