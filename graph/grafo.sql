--Nos
select distinct k.keyword_id as id, k.description as label, k.current_index  as size
    from cluster c
         join keyword_idea ki 
             on ki.idea_id = c.idea_id
         join keyword k 
             on ki.keyword_id = k.keyword_id
    where execution_id = 1
    and cluster_id = 38

--Arestas
select ky.description1 as source, ky.description2 as target, sum(ky.frequency) as weight
    from keyword_cooccurrence_per_year ky 	
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = 1 
                            and cluster_id = 38) as aux 
        on ky.keyword_id1 = aux.keyword_id 
        join (select ki.keyword_id from cluster c 
                                        join keyword_idea ki on c.idea_id = ki.idea_id and execution_id = 1 
                            and cluster_id = 38) as aux2
        on ky.keyword_id2 = aux2.keyword_id 
    group by ky.description1 , ky.description2
    