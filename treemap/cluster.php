<?php
 
require_once '..\dbconfig.php';

function getResult($conn,$exec) {
    $string = "";
    $sql = "select * from result where exec_id = '$exec' ";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getInfoCluster($conn,$execution,$cluster) {
    $string = "";
    $sql = "select id, identify, round(current_index,2) as current_index, qtd_ideia, qtd_pos, (qtd_pos*100)/qtd_ideia as percentage 
	from (SELECT c1.cluster_id as id, c1.cluster_id as identify, sum(c1.sum_current_index) as current_index, 
				count(c1.idea_id) as qtd_ideia
				FROM cluster c1	
                where c1.execution_id = $execution
                and c1.cluster_id = $cluster
				group by c1.cluster_id) aux 
		join (select c2.execution_id, c2.cluster_id ,count(*) as qtd_pos from cluster c2 
	where c2.polarization = 'POSITIVE'
    and c2.execution_id = $execution
    and c2.cluster_id = $cluster
	group by  c2.execution_id, c2.cluster_id) as aux2
		on aux2.cluster_id = aux.id";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
}

function getClusters($conn,$execution) {
    $string = "";
    $sql = "select id, identify, round(current_index,2) as current_index, qtd_ideia, qtd_pos, (qtd_pos*100)/qtd_ideia as percentage 
	from (SELECT c1.cluster_id as id, c1.cluster_id as identify, sum(c1.sum_current_index) as current_index, 
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

function getKeyword($conn,$execution,$cluster) {
    $string = "";
    $sql = "select distinct k.description_filter as name, round(k.current_index,2) as count
    from cluster c
         join keyword_idea ki 
             on ki.idea_id = c.idea_id
         join keyword k 
             on ki.keyword_id = k.keyword_id
    where execution_id = $execution 
    and cluster_id = $cluster";

    $string = '{';
    foreach ($conn->query($sql) as $row) {
        $string .=  '\''.$row["name"].'\''.':'.$row['count'].',';
    }
    $string = substr($string,0,-1);
    $string .='}';
    return $string;
}

function getKeyword2($conn,$execution,$cluster) {
    $string = "";
    $sql = "select distinct k.description as name, round(k.current_index,2) as count
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

function getTag($conn,$execution,$cluster) {
    $string = "";
    $sql = "select INITCAP(t.description) as description
    from cluster c
         join idea_tag it 
             on it.idea_id = c.idea_id
         join tag t 
             on t.tag_id = it.tag_id
    where execution_id = $execution 
    and cluster_id = $cluster";

    foreach ($conn->query($sql) as $row) {
        $string .= $row["description"].'|';
    }
    
    $string = str_replace('-','_',$string);

    return $string;
}

function getTag2($conn,$execution,$cluster) {
    $string = "";
    $sql = "select INITCAP(t.description) as name, count(*) as count, 
    from cluster c
         join idea_tag it 
             on it.idea_id = c.idea_id
         join tag t 
             on t.tag_id = it.tag_id
    where execution_id = $execution 
    and cluster_id = $cluster
    group by t.description ";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
    
}

function getIdeas($conn,$execution,$cluster) {
    $string = "";
    $sql = "select  round(i.current_index,2) as current_idx, i.* from idea i 
	join cluster c 
		on c.idea_id = i.idea_id
    where c.execution_id = $execution 
    and c.cluster_id = $cluster
    order by 1 desc";

    $statement = $conn->prepare($sql);
    $statement->execute();
    $results = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $results;
    
}

function getPapers($conn,$execution,$cluster) {
    $string = "";
    $sql = "select p.* 
    from cluster c 
        join idea i 
            on i.idea_id = c.idea_id
        join idea_paper ip 
            on ip.idea_id = i.idea_id
        join paper p
            on p.paper_id = ip.paper_id
    where c.execution_id = $execution 
    and c.cluster_id = $cluster
    order by p.year desc
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

if(isset($_GET['id'])){
   
    $id = explode('.',$_GET['id']);


    $dados = getResult($conn,$id[0]);

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

    $html = file_get_contents("../table3.html"); //read the file

$td = '<tr>
        <td>{cluster}</td>
        <td>{qtd_ideias}</td>
        <td>{indice_atualidade}</td>
        <td>{qtd_positivas}</td>
        <td>{perc_positivas}</td>  
        </tr>';
        
$htmlaux ='';


$dados = getInfoCluster($conn,$id['0'],$id['1']);



    $aux = $td;
    $aux = str_replace('{cluster}',$id[1],$aux);
    $aux = str_replace('{qtd_ideias}',$dados[0]['qtd_ideia'],$aux);
    $aux = str_replace('{indice_atualidade}',$dados[0]['current_index'],$aux);
    $aux = str_replace('{qtd_positivas}',$dados[0]['qtd_pos'],$aux);
    $aux = str_replace('{perc_positivas}',$dados[0]['percentage'].'%',$aux);
    $htmlaux.=$aux;


    $html = str_replace('{tr}',$htmlaux,$html);


    echo "<div style='width=100%;text-align: center;'> <h4> Dados do Cluster </h4> </div>";
    echo $html;

    if(isset($_GET['tagcloud'])){
        $dados = getKeyword($conn,$id[0],$id[1]);
       

        $html = file_get_contents("../tagcloud/tagcloud.html"); //read the file
        $html = str_replace('{tags}',$dados,$html);
        
        echo $html;

        

        echo '<hr>';
        $html = file_get_contents("../bubblechart/index.html"); //read the file
        $html = str_replace('{tags}',json_encode(getKeyword2($conn,$id[0],$id[1])),$html);

        echo $html;
  
        
        $htmlaux =   "<p >Para demonstração, o grafo abaixo foi gerado com a ferramenta Gephi.</p>
                    <p>A representação das cores dos nós, foi utilizada a classificação da ferramenta Gephi, 
                    onde os nós com mais conexões tem a cor de verde mais escura.</p>
                    <div style=\"height: 120px; \">
                        <div style=\"width: 30%; float:left;cursor:pointer; \" onclick=\"window.open('../images/grafo{idimg}.png')\";>
                            <img src=\"../images/grafo{idimg}_mini.png\" alt=\"Grafo\">
                            <p><strong>Clique aqui para abrir o Grafo do Cluster {cluster}</strong></p>
                        </div>
                    </div>";
        $htmlaux = str_replace('{idimg}',$id[0].'_'.$id[1],$htmlaux);
        $htmlaux = str_replace('{cluster}',$id[1],$htmlaux);

        $html = file_get_contents("../graph/grafo.html"); //read the file

        $filename = "../images/grafo".$id[0].'_'.$id[1]."_mini.png";

        if (file_exists($filename)) {
            $html = str_replace('{grafo}',$htmlaux,$html);
            $html = str_replace('{cluster}',$id[1],$html);
        } else{
            $html = str_replace('{grafo}','',$html);
            $html = str_replace('{cluster}','',$html);
        }
        

        $html = str_replace('{id}',$id[0].'.'.$id[1],$html);
        echo $html;



        $html = file_get_contents("table.html"); //read the file

        $td = '<tr>
        <th scope="row">{idea_id}</th>
        <td>{title}</td>
        <td>{content}</td>
        <td>{current_index}</td>
        </tr>';
        
        $htmlaux ='';

        $ideas = getIdeas($conn,$id[0],$id[1]);
        foreach ($ideas as $row) {
            $aux = $td;
            $aux = str_replace('{idea_id}',$row['idea_id'],$aux);
            $aux = str_replace('{title}',$row['title'],$aux);
            $aux = str_replace('{content}',$row['content'],$aux);
            $aux = str_replace('{current_index}',$row['current_idx'],$aux);
            $htmlaux.=$aux;
        }

        $td = '<tr>
        <th scope="row">{paper_id}</th>
        <td>{title}</td>
        <td>{abstract}</td>
        <td>{year}</td>
        </tr>';

        $html = str_replace('{tr}',$htmlaux,$html);
        
        /*$htmlaux ='';

        $papers = getPapers($conn,$id[0],$id[1]);
        foreach ($papers as $row) {
            $aux = $td;
            $aux = str_replace('{paper_id}',$row['paper_id'],$aux);
            $aux = str_replace('{title}',$row['title'],$aux);
            $aux = str_replace('{abstract}',$row['abstract'],$aux);
            $aux = str_replace('{year}',$row['year'],$aux);
            $htmlaux.=$aux;
        }

        $html = str_replace('{tr2}',$htmlaux,$html);
        */
        echo $html;

        

    } else {
        $html = file_get_contents("graph.html"); //read the file
        $html = str_replace('{execution}',$id[0],$html);
        $html = str_replace('{cluster}',$id[1],$html);
        echo $html;
       
    }
}
