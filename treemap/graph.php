<?php
 
require_once '..\dbconfig.php';
ini_set('max_execution_time', 500);

function getGraph($conn,$execution,$cluster) {
    $sql = "select ky.description1 as source, ky.description2 as target, sum(frequency) / 20 as value 
    from keyword_cooccurrence_per_year ky 	
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux 
        on ky.keyword_id1 = aux.keyword_id 
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux2
        on ky.keyword_id2 = aux2.keyword_id 
    group by ky.description1, ky.description2";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getGraph2($conn,$execution,$cluster) {
    $sql = "select ky.description1 as source, ky.description2 as target
    from keyword_cooccurrence_per_year ky 	
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux 
        on ky.keyword_id1 = aux.keyword_id 
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux2
        on ky.keyword_id2 = aux2.keyword_id 
    group by ky.description1, ky.description2";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getKeywords($conn,$execution,$cluster) {
    $sql = "select distinct k.description as id, 1 as group
    from cluster c
         join keyword_idea ki 
             on ki.idea_id = c.idea_id
         join keyword k 
             on ki.keyword_id = k.keyword_id
    where execution_id = $execution 
    and cluster_id = $cluster";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getNodes($conn,$execution,$cluster){
    $sql = "select distinct k.keyword_id as id, k.description as label, k.current_index  as size
    from cluster c
         join keyword_idea ki 
             on ki.idea_id = c.idea_id
         join keyword k 
             on ki.keyword_id = k.keyword_id
    where execution_id = $execution 
    and cluster_id = $cluster";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getEdges($conn,$execution,$cluster){
    $sql = "select ky.description1 as node1, ky.description2 as node2, sum(ky.frequency) as weight
    from keyword_cooccurrence_per_year ky 	
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux 
        on ky.keyword_id1 = aux.keyword_id 
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = $execution 
                            and cluster_id = $cluster) as aux2
        on ky.keyword_id2 = aux2.keyword_id 
    group by ky.description1 , ky.description2";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function geraGDF($dados){
    $arquivo = 'nodedef>name VARCHAR, width DOUBLE,height DOUBLE'.PHP_EOL;

    foreach ($dados['nodes'] as $value) {  
        $arquivo .= $value['label'].','.$value['size'].','.$value['size'].PHP_EOL;
    }
    $arquivo.='edgedef>node1 VARCHAR,node2 VARCHAR, weight DOUBLE'.PHP_EOL;

    foreach ($dados['edges'] as $value) {  
        $arquivo .= $value['node1'].','.$value['node2'].','.$value['weight'].PHP_EOL;
    }

    return $arquivo;
}

date_default_timezone_set('America/Sao_Paulo');
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


if(isset($_GET['id'])){
  //  $dados['nodes'] = getKeywords($conn,$_GET['execution'],$_GET['cluster']);
   // $list = getGraph($conn,$_GET['execution'],$_GET['cluster']);
    //$dados['links'] = $list;

    $id = explode('.',$_GET['id']);

    
    $dados['nodes'] = getNodes($conn,$id[0],$id[1]);
    $dados['edges'] = getEdges($conn,$id[0],$id[1]);

    $data = './files/File'.date("YmdHis").'-'.$_GET['id'].'.gdf'; 
    $fp = fopen( $data, 'a+');

    $aux = geraGDF($dados);
    
    fwrite($fp, $aux);
    fclose($fp);

    echo '<a href="'.$data.'">Download GDF</a>';

 
}



