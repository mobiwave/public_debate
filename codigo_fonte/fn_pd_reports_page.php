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
	<h2> Public Debate Reports </h2>
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

	fn_pd_report_total_comments_part($public_debate_id);
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	fn_pd_report_total_comments_user($public_debate_id);
}
?>
</div>