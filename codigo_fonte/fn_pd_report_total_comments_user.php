<?php
	
	// Register info for basic conectivity
	global $wpdb;
	global $public_debate_db_version;
	$table_consultas = $wpdb->prefix . "consultas";
	$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

	?>
	<h3> Total de Comentários por Usuário</h3>
	<table id="report_total_comments_part" class="widefat">
		<tr>
			<th width="60%">Nome do Usuário</th>
			<th>E-mail</th>
			<th style="text-align:center;">Total Comentários</th>
		</tr>

	<?php
		// Fidn the user who commented the debate
		$sql_authors  = "SELECT distinct(" . $wpdb->comments . ".comment_author_email), " . $wpdb->comments . ".comment_author
						FROM " . $wpdb->commentmeta . " 
						LEFT JOIN " . $wpdb->comments . " on " . $wpdb->commentmeta . ".comment_id = " . $wpdb->comments . ".comment_ID
						WHERE " . $wpdb->commentmeta . ".meta_key = %s and " . $wpdb->commentmeta . ".meta_value = %d";
   			                $meta_key = 'id_trecho_meta';
		$meta_key = 'id_consulta_meta';
		$meta_value = $public_debate_id;

		$comment_authors  = $wpdb->get_results($wpdb->prepare($sql_authors, $meta_key, $meta_value));

   		foreach($comment_authors as $c_author){
   			$sql_count_comment= "SELECT COUNT(" . $wpdb->comments . ".comment_author_email)
								FROM " . $wpdb->commentmeta . " 
								LEFT JOIN " . $wpdb->comments . " on " . $wpdb->commentmeta . " .comment_id = " . $wpdb->comments . ".comment_ID
								WHERE " . $wpdb->comments . ".comment_approved=1 AND " . $wpdb->commentmeta . " .meta_key = %s and " . $wpdb->commentmeta . " .meta_value = %d";
			$comment_count  = $wpdb->get_var($wpdb->prepare($sql_count_comment, $meta_key, $meta_value));

	?>
		<tr>
			<td><?php echo $c_author->comment_author; ?></td>
			<td><?php echo $c_author->comment_author_email; ?></td>
			<td style="text-align:center;"><?php echo $comment_count; ?></td>
		</tr>
	<?php
		}
	?>
	</table>