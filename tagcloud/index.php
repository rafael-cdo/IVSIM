<?php
 
require_once '..\dbconfig.php';

function getKeyword($conn, $year = 2018) {
    $string = "";
    $sql = "select k.description, kc.frequency, kc.frequency / 100 as peso 
    from keyword_frequency_per_year kc
        join keyword k
            on k.keyword_id = kc.keyword_id
    where kc.year = $year
    and kc.frequency > 100
    order by frequency desc
    limit 200";

    foreach ($conn->query($sql) as $row) {
        for ($i=0;$i<$row['peso'];$i++){
            $string .= $row["description"].'|';
        }
    }
    return $string;
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

if(isset($_POST['list'])){
    $year = $_POST['ano'];
    echo getKeyword($conn, $year);
    exit;
} else {
    $html = file_get_contents("index.html"); //read the file
    $html = str_replace('{tags}',getKeyword($conn),$html);
    echo $html;
}