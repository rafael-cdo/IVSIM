<?php
 
require_once 'dbconfig.php';

function getPaper($conn) {
    $sql = 'SELECT * FROM paper limit 100';
    foreach ($conn->query($sql) as $row) {
        print $row['paper_id'] . "-";
        print $row['title'] . "-";
        print $row['year'] . "<br>";
    }
}
 
$dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";
 
try{
    // create a PostgreSQL database connection
    $conn = new PDO($dsn);
 
    if(!$conn){
        echo "Erro ao conectar no banco!";
    }
}catch (PDOException $e){
    // report error message
    echo $e->getMessage();
}

getPaper($conn);