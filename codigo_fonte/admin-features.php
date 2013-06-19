<?php

add_action('admin_menu',"create_public_debate_admin_menu");
add_filter('admin_head','ShowTinyMCE');




function ShowTinyMCE() {
	// conditions here
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'jquery-color' );
	wp_print_scripts('editor');
	if (function_exists('add_thickbox')) add_thickbox();
	wp_print_scripts('media-upload');
	if (function_exists('wp_tiny_mce')) wp_tiny_mce();
	wp_admin_css();
	wp_enqueue_script('utils');
	do_action("admin_print_styles-post-php");
	do_action('admin_print_styles');
}





function create_public_debate_admin_menu(){
	//add_menu_page( "Public Debates" , "Public Debate" , "list-public-debates" , "list_public_debates" , "1" );
	//add_menu_page( 'Public Debate', 'Public Debate', 'manage_options', 'list-public-debates', 'list_public_debates', '', 120 );
	add_menu_page( 'Public Debates', 'Public Debates', 'manage_options', 'list-public-debates', 'list_public_debates' );
 	add_submenu_page('list-public-debates', "Add New Debate", "Add New Debate", 0, "mng-public-debate-form", "mng_public_debate_form");
 	add_submenu_page('list-public-debates', "Statistics", "Statistics", 0, "public-debate-reports", "fn_pd_reports_page");
 	add_submenu_page('list-public-debates', "Export Debate", "Export Debate", 0, "public-debate-export", "fn_pd_export_page");
}






function list_public_debates(){
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

if( isset($_GET["del"]) && $_GET["del"] != null ){
	$id_consulta_apagar = $_GET["del"];
	$wpdb->update( 
		$table_consultas, 
		array( 
			'situacaoConsulta' => '0'
		), 
		array( 'id' => $id_consulta_apagar ), 
		array( '%d'), array( '%d' ) 
	);

}

$items = mysql_num_rows(mysql_query("SELECT * FROM $table_consultas WHERE situacaoConsulta = 1")); // number of total rows in the database
 
if($items > 0) {
        $p = new pagination;
        $p->items($items);
        $p->limit(10); // Limit entries per page
        $p->target("admin.php?page=list-public-debates"); 
        $p->currentPage($_GET[$p->paging]); // Gets and validates the current page
        $p->calculate(); // Calculates what to show
        $p->parameterName('paging');
        $p->adjacents(1); //No. of page away from the current page
                 
        if(!isset($_GET['paging'])) {
            $p->page = 1;
        } else {
            $p->page = $_GET['paging'];
        }
         
        //Query for limit paging
        $limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
         
} else {
    echo "No Record Found";
}

?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br></div>
	<h2> Public Debates <a href="admin.php?page=mng-public-debate-form&add=true" class="add-new-h2">Add New</a> </h2>
	
	<div class="tablenav">
	    <div class='tablenav-pages'>
	        <?php echo $p->show();  // Echo out the list of paging. ?>
	    </div>
	</div>
<?php
if( isset($_GET["del"]) && $_GET["del"] != null ){
?>
<div class="updated"> <p>Consulta <strong> <?php echo $id_consulta_apagar; ?> </strong> removida com sucesso.</p></div>
<?php
}
?>
	<table class="widefat">
		<thead>
			<tr>
				<th>Nome da Consulta</th>
				<th>ID da Consulta</th>
				<th>Início</th>
				<th>Término</th>
				<th>Ações</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$consultas = $wpdb->get_results("SELECT * from $table_consultas WHERE situacaoConsulta = 1 ORDER BY id DESC $limit ");
				foreach($consultas as $consulta){
			?>
			<tr>
				<td><?php echo $consulta->nomeConsulta; ?></td>
				<td><?php echo $consulta->id; ?></td>
				<td><?php echo $consulta->dataInicioConsulta; ?></td>
				<td><?php echo $consulta->dataFimConsulta; ?></td>
				<td><a href="admin.php?page=mng-public-debate-form&edit=<?php echo $consulta->id; ?>">[editar]</a> - <a href="admin.php?page=list-public-debates&del=<?php echo $consulta->id; ?>" onclick="return confirm('Esta ação irá apagar todos os dados da consulta. Você tem certeza que deseja prosseguir?')">[excluir]</a></td>
			<?php
			}
			?>
			</tr>
		</tbody>
	</table>
</div>
<?php
}




function create_pd_parts($textoConsulta, $lastid){
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";


	$textoOriginal =  $textoConsulta;
//  Separava por Heading, paragrafos, listas (novo tinymmce no wordpress nao está mais apresentando isto. Atualmente quebra pela quebra de linha)
//	$textoOriginal = str_replace("\"", "'" , $textoOriginal);
//	$searchString = "#<[Pp].*</[Pp]>|<ul.*</ul>|<ol.*</ol>|<[Hh][0-9].*</[Hh][0-9]>#";
//	preg_match_all($searchString , $textoOriginal, $arrayTexto);

	$array_quebra_linha = explode("\n", $textoOriginal);
	$conta = $lastid[0]."000000000";
	$conta_parts = 0 ;
	foreach ($array_quebra_linha as $parts) {

		if (strlen($parts) > 5){
			$conta = $conta+1;
			$conta_parts = $conta_parts+1;
			$tamanho = strlen($parts);

			$wpdb->insert( 
				$table_consultas_trechos, 
				array( 
					'idTrecho' 			=> $conta, 
					'idConsulta' 		=> $lastid[0], 
					'trechoTexto' 		=> $parts, 
					'trechoComentavel' 	=> '1' 
				)
			);

		}

	}
	
	?>
	<div class="updated"> <p>Public Debate Created with a total of <strong> <?php echo $conta_parts; ?> </strong> parts.</p></div>
	<a href="admin.php?page=list-public-debates" class="add-new-h2">Voltar para Lista de Debates</a>  
	<a href="admin.php?page=mng-public-debate-form&edit=<?php echo $lastid[0]; ?>" class="add-new-h2">Selecionar Trechos Comentáveis</a>
	<?php

}



function add_new_debate(){
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

if( isset( $_POST["processaConsulta"]) && $_POST["processaConsulta"] == 'add_new_debate' ){
	$processaConsulta = $_POST["processaConsulta"];

	if( isset( $_POST["dataInicioConsulta"])){
		$dataInicioConsulta = $_POST["dataInicioConsulta"];
	}

	if( isset( $_POST["dataFimConsulta"])){
		$dataFimConsulta = $_POST["dataFimConsulta"];
	}
	if( isset( $_POST["nomeConsulta"])){
		$nomeConsulta = $_POST["nomeConsulta"];
	}
	if( isset( $_POST["responsaveisConsulta"])){
		$responsaveisConsulta = $_POST["responsaveisConsulta"];
	}
	if( isset( $_POST["descricaoConsulta"])){
		$descricaoConsulta = $_POST["descricaoConsulta"];
	}
	if( isset( $_POST["textoConsulta"])){
		$textoConsulta = $_POST["textoConsulta"];
	}

	$wpdb->insert( 
		$table_consultas, 
		array( 
			'dataInicioConsulta' 	=> $dataInicioConsulta, 
			'dataFimConsulta' 		=> $dataFimConsulta, 
			'nomeConsulta' 			=> $nomeConsulta, 
			'responsaveisConsulta' 	=> $responsaveisConsulta, 
			'descricaoConsulta' 	=> $descricaoConsulta, 
			'textoConsulta'			=> $textoConsulta,
			'situacaoConsulta'		=> '1'
		)
	);
	$lastid = $wpdb->get_col("SELECT ID FROM $table_consultas ORDER BY ID DESC LIMIT 0 , 1" );
	create_pd_parts( $textoConsulta , $lastid );
}

}


function render_add_new_debate_form(){
?>
<style type="text/css">
#pd_form_add_new li label {width: 150px; display: block;float: left;}
</style>

	<div id="poststuff">
		<form id="pd_form_add_new" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		    <ul>
				<li><label for="lname">Data Início: </label>
				<input id="lname" maxlength="45" size="10" name="dataInicioConsulta" value="" /></li>

				<li><label for="lname">Data Término: </label>
				<input id="lname" maxlength="45" size="10" name="dataFimConsulta" value="" /></li>

				<li><label for="fname">Título da Consulta: </label>
				<input id="fname" maxlength="45" style="width:443px" name="nomeConsulta" value="" /></li>    
				 
				<li><label for="lname">Responsáveis: </label>
				<input id="lname" maxlength="45" style="width:443px" name="responsaveisConsulta" value="" /></li>

				<li><label for="lname" style="vertical-align:top;">Descrição: <br />(Aceita HTML): </label>
				<textarea style="width:450px" rows="12" name="descricaoConsulta"></textarea></li>

				<li><label for="lname" style="vertical-align:top;">Texto da Consulta: </label>
				<div style="width:450px; float:left"><?php the_editor($content, 'textoConsulta', '','0','2', '1'); ?></div></li>
				
				<div class="clear"></div>

				<input type="hidden" id="lname" maxlength="45" size="10" name="processaConsulta" value="add_new_debate" />
				
				<input type="submit" value="Salvar">
		    </ul>
		</form>
	</div>
	<?php
}



function render_debate_update_form($idConsulta){
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

$dados_consulta = $wpdb->get_results("SELECT * from $table_consultas WHERE id = $idConsulta");
$dados_trechos = $wpdb->get_results("SELECT * from $table_consultas_trechos WHERE idConsulta = $idConsulta ORDER BY idTrecho ASC");


?>
	<style type="text/css">
	#pd_form_add_new li label {width: 150px; display: block;float: left;}
	</style>

	<div id="poststuff">
		<form id="pd_form_add_new" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
		    <ul>
				<li><label for="lname">Data Início: </label>
				<input id="lname" maxlength="45" size="10" name="dataInicioConsulta" value="<?php echo $dados_consulta[0]->dataInicioConsulta ; ?>" /></li>

				<li><label for="lname">Data Término: </label>
				<input id="lname" maxlength="45" size="10" name="dataFimConsulta" value="<?php echo $dados_consulta[0]->dataFimConsulta ; ?>" /></li>

				<li><label for="fname">Título da Consulta: </label>
				<input id="fname" maxlength="45" style="width:443px" name="nomeConsulta" value="<?php echo $dados_consulta[0]->nomeConsulta ; ?>" /></li>    
				 
				<li><label for="lname">Responsáveis: </label>
				<input id="lname" maxlength="45" style="width:443px" name="responsaveisConsulta" value="<?php echo $dados_consulta[0]->responsaveisConsulta ; ?>" /></li>

				<li><label for="lname" style="vertical-align:top;">Descrição: <br />(Aceita HTML): </label>
				<textarea style="width:450px" rows="12" name="descricaoConsulta"><?php echo $dados_consulta[0]->descricaoConsulta ; ?></textarea></li>

				<li><label for="lname" style="vertical-align:top;">Trechos da Consulta: </label>
				<div style="width:450px; float:left">
					<table class="widefat">
						<tr>
							<th>Comentável</th>
							<th>Texto </th>
						</tr>
					<?php
					foreach($dados_trechos as $trechos){
						if ($trechos->trechoComentavel == 1){
							$checked = 'checked = "checked"';
						}else{
							$checked = '';
						}
					?>
						<tr>
							<td><input <?php echo $checked ; ?> type="checkbox" name="trechosComentaveis[]" value="<?php echo $trechos->idTrecho ; ?>"></td>
							<td><?php echo $trechos->trechoTexto ; ?></td>
						</tr>
					<?php
					}
					?>
					</table>
				</div>
				</li>
				
				<div class="clear"></div>

				<input type="hidden" id="lname" maxlength="45" size="10" name="processaConsulta" value="update_debate" />
				<input type="hidden" id="lname" maxlength="45" size="10" name="idConsulta" value="<?php echo $dados_consulta[0]->id ; ?>" />
				
				<input type="submit" value="Salvar">
		    </ul>
		</form>
	</div>
	<?php
}





function update_debate(){
global $wpdb;
global $public_debate_db_version;

$table_consultas = $wpdb->prefix . "consultas";
$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";


	$idConsulta = $_POST["idConsulta"];

	if( isset( $_POST["dataInicioConsulta"])){
		$dataInicioConsulta = $_POST["dataInicioConsulta"];
	}

	if( isset( $_POST["dataFimConsulta"])){
		$dataFimConsulta = $_POST["dataFimConsulta"];
	}
	if( isset( $_POST["nomeConsulta"])){
		$nomeConsulta = $_POST["nomeConsulta"];
	}
	if( isset( $_POST["responsaveisConsulta"])){
		$responsaveisConsulta = $_POST["responsaveisConsulta"];
	}
	if( isset( $_POST["descricaoConsulta"])){
		$descricaoConsulta = $_POST["descricaoConsulta"];
	}
	if( isset( $_POST["trechosComentaveis"])){
		$trechosComentaveis = $_POST["trechosComentaveis"];
	}

	//update all public debate data
		$wpdb->update( 
		$table_consultas, 
		array( 
			'dataInicioConsulta' => $dataInicioConsulta,
			'dataFimConsulta' => $dataFimConsulta,
			'nomeConsulta' => $nomeConsulta,
			'descricaoConsulta' => $descricaoConsulta,
			'responsaveisConsulta' => $responsaveisConsulta
		), 
		array( 'id' => $idConsulta ), 
		array( '%s','%s','%s','%s','%s'), array( '%s','%s','%s','%s','%s' ) 
	);

	//update all parts as not commentable
		$wpdb->update( 
		$table_consultas_trechos, 
		array( 
			'trechoComentavel' => '0'
		), 
		array( 'idConsulta' => $idConsulta ), 
		array( '%d' ), array( '%d' ) 
	);

	//activate the commentable part set by the user

		foreach ($trechosComentaveis as $trecho) {
			$idTrecho = $trecho;
			$wpdb->update( 
				$table_consultas_trechos, 
				array( 
					'trechoComentavel' => '1'
				), 
				array( 'idTrecho' => $idTrecho ), 
				array( '%d' ), array( '%d' ) 
				);
		}

}




function mng_public_debate_form(){
/* list of Management Pages
edit_pd_form -> Show Public Debate Values for Editing
edit_pd_save -> Save Public debate New Values
add_pd_form -> Show Public Debate Form for Creating a New Debate
add_pd_save -> Create New Public Debate
*/
?>
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br></div>
	<h2> Add New Public  Debate </h2>

	<?php

	if( isset( $_POST["processaConsulta"]) && $_POST["processaConsulta"] == 'add_new_debate' ){
		add_new_debate();
	} 

	if( isset( $_POST["processaConsulta"]) && $_POST["processaConsulta"] == 'update_debate' ){
		update_debate();
	} 

	if( isset( $_GET["edit"] ) && $_GET["edit"] != NULL ){
		render_debate_update_form($_GET["edit"]);
	} 

	if( isset( $_GET["add"] ) && $_GET["add"] != NULL ){
		render_add_new_debate_form();
	} 
	
	if( !isset( $_GET["edit"]) && !isset( $_GET["add"])  && !isset( $_POST["processaConsulta"])){
		render_add_new_debate_form();
	} 

?>
</div>
<?php
}


// BEGIN OF REPORTS

function fn_pd_reports_page(){
	require("fn_pd_reports_page.php");
}

function fn_pd_report_total_comments_part($public_debate_id){
	require("fn_pd_report_total_comments_part.php");
}

function fn_pd_report_total_comments_user($public_debate_id){
	require("fn_pd_report_total_comments_user.php");
}

function fn_pd_export_page(){
require("fn_pd_export_page.php");
}

?>