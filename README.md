# crutch/database-pdo

Database PDO implementation

# Install

```bash
composer require crutch/database-pdo
```

# Usage

```php
<?php

/** @var \PDO $pdo */
$database = new \Crutch\DatabasePdo\DatabasePdo($pdo);

$database->execute('INSERT INTO table (id, value) VALUES (?, ?)', [1, 'it is works']);

$query = 'SELECT * FROM table WHERE id = ?';
$oneRow = $database->fetch($query, [1]);
// $oneRow = ['id' => 1, 'value' => 'it is works'];

$allRows = $database->fetchAll($query, [1]);
// $allRows = [['id' => 1, 'value' => 'it is works']];

$database->begin();
try {
    $database->execute('DELETE FROM table WHERE id = :id', ['id' => 1]);
    $database->commit();
} catch (\Crutch\Database\Exception\StorageError $exception) {
    $database->rollback();
}
```
