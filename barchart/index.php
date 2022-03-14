<?php
 
require_once '..\dbconfig.php';

function getPapers($conn,$keyword) {
    $string = "";
    $sql = "select * 
    from paper p
        join paper_keyword pk
            on p.paper_id = pk.paper_id
        join keyword k 
            on k.keyword_id = pk.keyword_id
        join paper_keyword2 pk2
            on pk.paper_id = pk2.paper_id            
    where k.description_filter = '$keyword'
    and p.year between 2000 and 2017
    order by year desc
    limit 50";

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

$html = file_get_contents("index.html"); //read the file

if(isset($_GET['keyword'])){
    $html = str_replace('{keyword}',$_GET['keyword'],$html);
}
echo '<div style="width: 100%;text-align: center"><h4>Papers Publicados</h4></div>';

echo '<p>Afim de corroborar com a tag cloud, podemos utilizar esta técnica visual para criar uma 
análise temporal das publicações cientificas referentes ao termo.</p> 
<p>Está técnica utiliza o uso de barras horizontais ou barras verticais. 
Nesta aplicação, o eixo vertical representa a frequência dos termos nas publicações da base científica, 
e o eixo horizontal representa o ano da publicação.</p>
<p>Clicando nas barras, será filtrado os papers de determinado ano.</p>';

echo '<h4>Keyword: '.$_GET['keyword'].'</h4>';

echo $html;

$html = file_get_contents("table.html"); //read the file

$td = '<tr>
        <th scope="row">{paper_id}</th>
        <td>{title}</td>
        <td>{keywords}</td>
        <td>{abstract}</td>
        <td>{year}</td>
        </tr>';
        
$htmlaux ='';

$papers = getPapers($conn,$_GET['keyword']);
        foreach ($papers as $row) {
            $aux = $td;
            $aux = str_replace('{paper_id}',$row['paper_id'],$aux);
            $aux = str_replace('{title}',$row['title'],$aux);
            $aux = str_replace('{abstract}',$row['abstract'],$aux);
            $aux = str_replace('{keywords}',$row['keywords'],$aux);
            $aux = str_replace('{year}',$row['year'],$aux);
            $htmlaux.=$aux;
        }

$html = str_replace('{tr}',$htmlaux,$html);
echo $html;