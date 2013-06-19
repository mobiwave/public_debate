<?php
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";
?>
<style type="text/css">
	#pd_form_select_report li label {width: 150px; display: block;float: left;}
	#pd_form_select_report li select {width:350px;}
</style>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br></div>
	<h2> Export Public Debate </h2>
<div id="poststuff">
	<form id="pd_form_select_report" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<ul>
		<li>
			<label for="lname">Selecione o Debate: </label>
			<select name="public_debate">
				<option> -- Selecione -- </option>
				<?php
				$debates = $wpdb->get_results("SELECT * from $table_consultas WHERE situacaoConsulta =1 ORDER BY id DESC ");
				foreach($debates as $debate){
				?>
				<option value="<?php echo $debate->id; ?>"> <?php echo $debate->nomeConsulta; ?> </option>
				<?php
				}
				?>
			</select>
		</li>
		<!-- Selecionar o Tipo de Relatório 
		<li>
			<label for="lname">Selecione o Relatório:</label>
			<select name="report">
				<option> -- Selecione -- </option>
				<option value=""> Total de Comentários Por Trechos </option>
				<option value=""> Total de Comentários Por Usuários </option>
				<option value=""> 10 trechos mais Comentados </option>
				<option value=""> 10 usuários mais ativos </option>
				<option value=""> Extrair todos os Comentários </option>
			</select>
		</li>
		-->
		<li>
			<label for="lname">&nbsp;</label>
			<input type="submit" value="Ok">
		</li>
	</ul>
<?php
if (isset($_POST["public_debate"]) && $_POST["public_debate"] != NULL ) {

	$public_debate_id = $_POST["public_debate"];

	$debate_export_parts = $wpdb->get_results( 
	"SELECT   
		ct.trechoTexto as part_text
		,cm.meta_value as part_id
		,c.comment_author as user
		,c.comment_author_email as email
		,c.comment_date  as date
		,c.comment_content as content
		from $wpdb->consultas_trechos ct
		LEFT JOIN $wpdb->commentmeta cm on ct.idTrecho = cm.meta_value
		LEFT JOIN $wpdb->comments c on cm.comment_id = c.comment_ID
		where cm.comment_id is not null
		and ct.idConsulta = $public_debate_id
		ORDER BY ct.idTrecho ASC, c.comment_date ASC ");

	?>

	<h1 class='titulo-consulta'>Comentários da Consulta por Trechos</h1>
	<table class="widefat">
		<tr>	
			<th> Trecho </th>
			<th> Id Trecho </th>
			<th> Usuário </th>
			<th width="150px"> E-mail</th>
			<th> Data </th>
			<th> Comentários </th>
		</tr>
		<?php
		foreach ($debate_export_parts as $part) {
		?>
		<tr>	
			<td> <?php echo $part->part_text ?></td>
			<td> <?php echo $part->part_id ?> </td>
			<td> <?php echo $part->user ?></td>
			<td> <?php echo $part->email ?></td>
			<td> <?php echo $part->date ?> </td>
			<td> <?php echo $part->content ?> </td>
		</tr>
		<?php
	}
		?>
	</table><br /><br /><br /><br />


	<?php
	$debate_export_comments = $wpdb->get_results( 
	"SELECT 
		c.comment_author as user
		,c.comment_author_email as email
		,c.comment_date  as date
		,c.comment_content as content
		,cm.meta_value as part_id
		,ct.trechoTexto as part_text
		from $wpdb->comments c
		left join $wpdb->commentmeta cm on c.comment_ID = cm.comment_id 
		left join $wpdb->consultas_trechos ct on ct.idTrecho = cm.meta_value
		WHERE cm.meta_key = 'id_trecho_meta' 
		and ct.idConsulta = $public_debate_id ");

	?>

	<h1 class='titulo-consulta'>Comentários da Consulta por Usuários</h1>
	<table class="widefat">
		<tr>	
			<th> Usuário </th>
			<th > E-mail </th>
			<th width="150px"> Data</th>
			<th> Comentário </th>
			<th> ID Trecho </th>
			<th> Trecho </th>
		</tr>
		<?php
		foreach ($debate_export_comments as $comments) {
		
		?>
		<tr>	
			<td> <?php echo $comments->user ?></td>
			<td> <?php echo $comments->email ?> </td>
			<td> <?php echo $comments->date ?></td>
			<td> <?php echo $comments->content ?></td>
			<td> <?php echo $comments->part_id ?> </td>
			<td> <?php echo $comments->part_text ?> </td>
		</tr>
		<?php
	}
		?>
	</table>
<?php } ?>
</div>