<?php
require_once '..\dbconfig.php';

function getKeywords($conn, $keyword) {
    $sql = "select kc.year as year, kc.frequency as Freq
    from keyword_frequency_per_year kc 
	join keyword k 
		on k.keyword_id = kc.keyword_id
    where lower(k.description)= '$keyword'
    and kc.year between 2000 and 2017
    order by year 
    limit 30";
    
    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $json = json_encode($results);
    return $json;
}

function getKeywordByYear($conn, $keyword, $year) {
    $sql = "select * 
    from paper p
        join paper_keyword pk
            on p.paper_id = pk.paper_id
        join keyword k 
            on k.keyword_id = pk.keyword_id
        join paper_keyword2 pk2
            on pk.paper_id = pk2.paper_id            
    where k.description_filter = '$keyword'
    and p.year = '$year'
    order by year desc
    limit 50";
    
    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    $json = json_encode($results);
    return $json;
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

if(isset($_GET['year'])){
    echo getKeywordByYear($conn,$_GET['keyword'],$_GET['year']);
    exit;
} elseif(isset($_GET['keyword'])){
    
    echo getKeywords($conn,$_GET['keyword']);
    exit;
}

