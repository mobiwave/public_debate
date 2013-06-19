<?php
	global $wpdb;
	global $public_debate_db_version;

	$table_consultas = $wpdb->prefix . "consultas";
	$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

?>



<?php
	$debate_parts = $wpdb->get_results( 
	" SELECT * from $wpdb->consultas_trechos 
	  WHERE idConsulta = $public_debate_id 
	  ORDER BY idTrecho ASC");
	?>

	<br/>
	<br/>
	<h3> Total de Comentários por Trecho</h3>
	<table id="report_total_comments_part" class="widefat">
		<tr>
			<th width="80%">Trecho</th>
			<th style="text-align:center;" width="20%">Total Comentários</th>
		</tr>
<?php
// Loop through parts rendering comment links
foreach ($debate_parts as $part){
	$comment_count = "0";
	if($part->trechoComentavel == 1){
		global $id_trecho_meta;
		global $id_consulta_meta;
	    $id_trecho_meta = $part->idTrecho;
	    $id_trecho = $part->idTrecho;
		$id_consulta_meta = $part->idConsulta;
	
	// Begin count comments on debate parts
	$sql  = 'SELECT count(*) FROM ' . $wpdb->comments . ' comments '
        . ' INNER JOIN ' . $wpdb->commentmeta . ' meta ON comments.comment_ID = meta.comment_id '
        . ' WHERE comments.comment_approved=1 AND meta.meta_key = %s AND meta.meta_value = %s ';
    $meta_key = 'id_trecho_meta';
    $meta_value = $id_trecho;
    $comment_count   = $wpdb->get_var($wpdb->prepare($sql, $meta_key, $meta_value));

    if($comment_count < 10){
    	settype($comment_count, "string");
    	$comment_count = "".$comment_count;
    	}
    // Finish counting comments on debate parts
	}
	?>
		<tr>
			<td><?php echo $part->trechoTexto; ?></td>
			<td style="text-align:center;"><strong><?php echo $comment_count ?></strong></td>
		</tr>

	<?php

	}
	?>
	</table>