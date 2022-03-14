<?php
 
require_once '..\dbconfig.php';


function getClusters($conn,$execution) {
    $string = "";
    $sql = "select id, identify, current_index, qtd_ideia, qtd_pos, (qtd_pos*100)/qtd_ideia as percentage 
	from (SELECT c1.cluster_id as id, c1.cluster_id as identify, sum(c1.current_index) as current_index, 
				count(c1.idea_id) as qtd_ideia
				FROM cluster c1	
				where c1.execution_id = $execution
				group by c1.cluster_id) aux 
		join (select c2.execution_id, c2.cluster_id ,count(*) as qtd_pos from cluster c2 
	where c2.polarization = 'POSITIVE'
	and c2.execution_id = $execution
	group by  c2.execution_id, c2.cluster_id) as aux2
		on aux2.cluster_id = aux.id";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
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

if(isset($_GET['execution'])){
    $execution = $_GET['execution'];
} else {
    $execution = 1;
}

$dados = getClusters($conn,$execution);
$arr = array();

$arr['identify'] = $execution;
$arr['id'] = $execution;
$arr['description'] = 'Execution '.$execution;
$arr['children'] = $dados;

echo json_encode($arr);
 

exit;


