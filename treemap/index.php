<?php

require_once '../dbconfig.php';


function getResult($conn,$exec) {
    $string = "";
    $sql = "select * from result where exec_id = '$exec' ";

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
    $exec = $_GET['execution'];
} else {
    $exec = 18;
}

$dados = getResult($conn,$exec);

$arr = array();

$html = file_get_contents("../table2.html"); //read the file

$td = '<tr {color} onclick="window.open(\'/treemap/?execution={exec_id}\');">
        <td>{session_id}</td>
        <td>{cenario}</td>
        <td>{exec_id}</td>
        <td>{nr_clusters}</td>
        <td>{avg_pol}</td>
        <td>{avg_imp}</td>
        <td>{sum_idx_atual}</td>
        <td>{limiar}</td>
        <td>{idea_info}</td>
        <td>{keyword_info}</td>
        <td>{cur_idx_info}</td>
        <td>{neutral}</td>
        <td>{normalized_cur_idx}</td>
        </tr>';
        
$htmlaux ='';

foreach ($dados as $row) {
    $aux = $td;
    $aux = str_replace('{session_id}',$row['session_id'],$aux);
    $aux = str_replace('{cenario}',$row['cenario'],$aux);
    $aux = str_replace('{exec_id}',$row['exec_id'],$aux);
    $aux = str_replace('{nr_clusters}',$row['nr_clusters'],$aux);
    $aux = str_replace('{avg_pol}',$row['avg_pol'],$aux);
    $aux = str_replace('{avg_imp}',$row['avg_imp'],$aux);
    $aux = str_replace('{sum_idx_atual}',$row['sum_idx_atual'],$aux);
    $aux = str_replace('{limiar}',$row['limiar'],$aux);
    $aux = str_replace('{idea_info}',$row['idea_info'],$aux);
    $aux = str_replace('{keyword_info}',$row['keyword_info'],$aux);
    $aux = str_replace('{cur_idx_info}',$row['cur_idx_info'],$aux);
    $aux = str_replace('{neutral}',$row['neutral'],$aux);
    $aux = str_replace('{normalized_cur_idx}',$row['normalized_cur_idx'],$aux);
    if($row['exec_id'] == '17' || $row['exec_id'] == '18'){
        $aux = str_replace('{color}','style="color:green;"',$aux);
    }
    $htmlaux.=$aux;
}

$html = str_replace('{tr}',$htmlaux,$html);
echo "<div style='width=100%;text-align: center;'> <h4> Dados da execução </h4> </div>";
echo $html;


 
$html = file_get_contents("index.html"); //read the file

if(isset($_GET['execution'])){
    $html = str_replace('{execution}',$_GET['execution'],$html);
    $html = str_replace('{execution'.$_GET['execution'].'}','selected',$html);
} else {
    $html = str_replace('{execution}',18,$html);
}



echo $html;