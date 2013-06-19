<?php
/*
	Plugin Name: Public Debate
	Version: 0.1 Beta
	Plugin URI: None Yet
	Description: Let users comment on post paragraphs, titles and lists instead the post as a whole thing.
	Text Domain: public-debate
	Domain Path: /lang
	Author: Matheus Neves
	Author URI: http://www.matheusneves.com.br/
 */

require("pagination.class.php");
require("admin-features.php");




if (!isset($wpdb->consultas)) {
	$wpdb->consultas = $table_prefix . 'consultas';
}
if (!isset($wpdb->consultas_trechos)) {
	$wpdb->consultas_trechos = $table_prefix . 'consultas_trechos';
}

register_activation_hook( __FILE__, 'public_debate_install' );
register_activation_hook( __FILE__, 'public_debate_install_data' );
register_deactivation_hook(__FILE__, 'public_debate_uninstall');





// Install the Plugin

global $public_debate_db_version;
$public_debate_db_version = "0.1";

function public_debate_install() {
   global $wpdb;
   global $public_debate_db_version;

   $table_consultas = $wpdb->prefix . "consultas";
   $table_consultas_trechos = $wpdb->prefix . "consultas_trechos";
      
   $sql = "CREATE TABLE " . $table_consultas . "  (
  id int(11) NOT NULL AUTO_INCREMENT,
  nomeConsulta tinytext NOT NULL,
  textoConsulta int(11) NOT NULL,
  descricaoConsulta tinytext NOT NULL,
  responsaveisConsulta varchar(255) NOT NULL,
  dataInicioConsulta varchar(10) NOT NULL,
  dataFimConsulta varchar(10) NOT NULL,
  situacaoConsulta tinyint(4) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE " . $table_consultas_trechos . "  (
  idTrecho bigint(20) NOT NULL,
  idConsulta bigint(20) NOT NULL,
  trechoTexto text NOT NULL,
  trechoComentavel tinyint(4) NOT NULL,
  trechoComentarios int(11) NOT NULL,
  PRIMARY KEY (idTrecho),
  UNIQUE KEY idTrecho (idTrecho)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $sql );
 
   add_option( "public_debate_db_version", $public_debate_db_version );
}

function public_debate_install_data() {
   global $wpdb;
   $welcome_name = "Mr. WordPress";
   $welcome_text = "Parabéns, plugin de consulta pública, versão alpha instalada";

   $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' => $welcome_text ) );
}



// Uninstall the plugin

function public_debate_uninstall() {

   global $wpdb;
   global $public_debate_db_version;

   $table_consultas = $wpdb->prefix . "consultas";
   $table_consultas_trechos = $wpdb->prefix . "consultas_trechos";

  $wpdb->query ("DROP TABLE IF EXISTS $table_consultas");
  $wpdb->query ("DROP TABLE IF EXISTS $table_consultas_trechos");
}












// Add meta box on page admin to enter the debate ID

add_action( 'add_meta_boxes', 'public_debate_id_meta_box_add' );
add_action( 'save_post', 'public_debate_id_meta_box_save' );

function public_debate_id_meta_box_add(){
	add_meta_box( 'public_debate_id_meta_box', 'Insert Debate ID', 'public_debate_id_meta_box_render' ,'page', 'side', 'high' );	
}

function public_debate_id_meta_box_render( $post ){

	// get meta value public-debate-id
	$values = get_post_custom ( $post->ID );
	$public_debate_id = isset( $values['public_debate_id'] ) ? esc_attr( $values['public_debate_id'] [0] ) : '' ;

	// render the field to enter the public debate id
	?>
	<label for="public_debate_id"> Debate ID: </label>
	<select name="public_debate_id">
		<option>-- Selecione --</option>
		<?php
		global $wpdb;
		global $public_debate_db_version;

		$table_consultas = $wpdb->prefix . "consultas";
		$table_consultas_trechos = $wpdb->prefix . "consultas_trechos";
		$consultas = $wpdb->get_results("SELECT * from $table_consultas WHERE situacaoConsulta = 1 ORDER BY id DESC ");
		foreach($consultas as $consulta){
				if( $consulta->id == $public_debate_id){
					$selected = 'Selected = "Selected" ';  
				} else{
					$selected= '';
				}
		?>
		<option <?php echo $selected; ?> value="<?php echo $consulta->id; ?>"> <?php echo $consulta->nomeConsulta; ?>  - <?php echo $consulta->dataInicioConsulta; ?></option>
		<?php
	}
	?>
	</select>
	<?php
}

function public_debate_id_meta_box_save ( $post_id ) {
	
	// stop if autosave is active
	if ( defined ( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

	// stop if user can't edit the post
	if (!current_user_can( 'edit_post' )) return;

	if( isset( $_POST['public_debate_id']))
		update_post_meta( $post_id, 'public_debate_id', esc_attr( $_POST['public_debate_id'] ));
}


// Change page output to display debate paragraphs

add_filter( 'the_content', 'is_public_debate', 10000 );

function is_public_debate( $content ){
	echo $content;

	// Check if the page is a debate by checking for the meta public debate id
	$my_post_id = get_the_ID();
	$values = get_post_custom ( $my_post_id );
	$public_debate_id = isset( $values['public_debate_id'] ) ? esc_attr( $values['public_debate_id'] [0] ) : '' ;
	
	if(is_page() == TRUE && isset( $values['public_debate_id'] ) ){
		// call the function to render the debate
		render_public_debate($public_debate_id);
	} 

}

function render_public_debate($public_debate_id){
//******* Abre Div Principal da Consulta *******
echo '<div id="consulta">'; //
//******* Abre Div Principal da Consulta *******

	$plugin_root_url = plugins_url();
	$plugin_root_url = $plugin_root_url.'/public-debate/';

	// Temporary javascript to hide excessive divs - substitute it later for any jquery animated plugin
	?>
	<script type="text/javascript">
	function mostraFormulario(currentFormId){
		if (document.getElementById(currentFormId).style.display != "block"){
			document.getElementById(currentFormId).style.display = "block"
		}else{
			document.getElementById(currentFormId).style.display = "none"
		}
	}
	function mostraComentarios(currentComentarios){
		if (document.getElementById(currentComentarios).style.display != "block"){
			document.getElementById(currentComentarios).style.display = "block"
		}else{
			document.getElementById(currentComentarios).style.display = "none"
		}
	}

	</script>
	<style type="text/css">
	.pd_part {}
	.pd_part textarea{border:1px solid #ddd;}
	.pd_part_text{background-color:#f6f6f6; padding: 2px; margin: 2px 5px 5px 70px; border-left: 3px solid #ddd;}
	.pd_part_comments_write_form {display: none;}
	.pd_part_comments_list{ display: none;}
	#content .pd_part_text p {margin: 0px;}

	.pd_part_options { float:left; width:90px; }
	.pd_part_options a {float:left; display: block; width: 24px; height: 24px; overflow: hidden; text-indent: -500px;}
	.pd_part_list_icon {background: url('<?php echo $plugin_root_url ?>/img/comment_bubble.png') 0px 48px;}
	.pd_part_write_icon {background: url('<?php echo $plugin_root_url ?>/img/comment_write.png') 0px 48px;}
	.pd_part_options a:hover {background-position: 0px 24px}
	.pd_part_options span{ display: block;background-color: #f6f6f6; font-size: 10px; font-weight: bold}
	
	.pd_part_comment_box { background-color: #fff; border:1px solid #ddd; padding:5px;font-weight: normal; font-size: 12px; }
	.pd_part_comment_head { display: block; float:none; line-height: 30px; vertical-align: top; }
	.pd_part_comment_head a {line-height: 30px; vertical-align: top; }
	.pd_part_comment_date { display: block; margin-bottom: 5px;text-align: right;}

	
	 div.pd_part .pd_part_options {

}


 div.pd_part:hover .pd_part_options {
display: block;
}
	</style>
	<?php



	// Check if the user provided a valid public debate id
	global $is_consulta_aberta;
	global $wpdb;
	$debate_info = $wpdb->get_results( 
		" SELECT * FROM $wpdb->consultas 
		  WHERE id = $public_debate_id 
		  LIMIT 1");

	/* Block comments depending o public debate dates
	   Needs to be fixed data comaprison is not working

	$data_atual = date("d/m/Y" );
	if($data_atual < $debate_info[0]->dataInicioConsulta)
		$is_consulta_aberta = "Consulta ainda não Iniciada";

	if($data_atual > $debate_info[0]->dataFimConsulta)
		$is_consulta_aberta = "Consulta Encerrada";
	*/

	$my_post_id = get_the_ID();
	if ( comments_open ( $my_post_id ) == FALSE) 
		$is_consulta_aberta = "Consulta Fechada para Comentários";


	
	if (sizeof($debate_info) > 0) {
		// Selects public debate parts
		$debate_parts = $wpdb->get_results( 
			" SELECT * from $wpdb->consultas_trechos 
			  WHERE idConsulta = $public_debate_id 
			  ORDER BY idTrecho ASC");

		// Loop through parts rendering comment links
		echo "<h1 class='titulo-consulta'>Texto em Consulta</h1> ";
		foreach ($debate_parts as $part){
			if($part->trechoComentavel == 1){
				global $id_trecho_meta;
    			global $id_consulta_meta;
			    $id_trecho_meta = $part->idTrecho;
			    $id_trecho = $part->idTrecho;
		    	$id_consulta_meta = $part->idConsulta;
				
		    	// Begin count comments on debate parts
		    	$sql  = 'SELECT count(*) FROM ' . $wpdb->comments . ' comments '
                    . ' INNER JOIN ' . $wpdb->commentmeta . ' meta ON comments.comment_ID = meta.comment_id '
                    . ' WHERE meta.meta_key = %s AND meta.meta_value = %s ';
                $meta_key = 'id_trecho_meta';
                $meta_value = $id_trecho;
                $comment_count   = $wpdb->get_var($wpdb->prepare($sql, $meta_key, $meta_value));

                if($comment_count < 10){
                	settype($comment_count, "string");
                	$comment_count = "0".$comment_count;
                }
                // Finish counting comments on debate parts
                
                // Print the right optins of the public debate
                if( comments_open ( $my_post_id ) == TRUE )
		    		$is_consulta_aberta = '<a class="pd_part_write_icon" href="javascript:void(0)" onclick="mostraFormulario(\'formulario-'.$part->idTrecho.'\');"> Comentar </a> ';
		    	
				?>
				<div class="pd_part">
					<div class="pd_part_options">
						<?php echo $is_consulta_aberta; ?>
						<a class="pd_part_list_icon" href="javascript:void(0)" onclick="mostraComentarios('comentarios-<?php echo $part->idTrecho; ?>');"> Comentários (<?php echo $comment_count; ?>) </a> 
						<span>(<?php echo $comment_count; ?>)</span>
					</div>
					<div class="pd_part_text">
						<?php echo $part->trechoTexto; ?>

						<div class="pd_part_comments_list" id="comentarios-<?php echo $part->idTrecho; ?>">
							<?php  get_public_debate_comments( $id_trecho ) ?>
						</div>
						<div class="pd_part_comments_write_form" id="formulario-<?php echo $part->idTrecho; ?>">
							<?php comment_form(); ?>
						</div>
					</div>
				</div>
				<?php
				$id_trecho_meta = "none";
		    	$id_consulta_meta = $part->idConsulta;
			} else {
				/* set value for message if consulta is aberta and part is not commentable
				 if ( comments_open ( $my_post_id ) == TRUE )
				 		$is_consulta_aberta = "Comentário desabilitado";
				 */
				?>
				<div class="pd_part">
					<div class="pd_part_text">
					<?php echo $part->trechoTexto; ?>
					<!--
					<div class="pd_part_comments_otions">
						<i> <?php echo $is_consulta_aberta; ?> </i>
					</div>
					-->
					</div>
				</div>
				<?php
			}
		}
	}
//******* Fecha Div Principal da Consulta *******
echo '</div>'; //
//******* Fecha Div Principal da Consulta *******
}



// Alter the contact form function to include paragraphs ID and debate ID meta information
add_action( 'comment_form_logged_in_after', 'public_debate_extra_fields' );
add_action( 'comment_form_after_fields', 'public_debate_extra_fields' );
add_filter( 'comment_form_defaults', 'public_debate_defaults' );
add_action( 'comment_post', 'public_debate_save_comment' );


function public_debate_defaults($defaults) {
    $defaults['comment_notes_after'] = '';
    return $defaults;
}

function public_debate_extra_fields () {
    global $id_trecho_meta;
    global $id_consulta_meta;

	?>
        <input type="hidden" name="id_trecho_meta" id="id_trecho_meta-<?php echo $id_trecho_meta; ?>" value="<?php echo $id_trecho_meta; ?>" />
        <input type="hidden" name="id_consulta_meta" id="id_consulta_meta" value="<?php echo $id_consulta_meta; ?>" />
	<?php
}

function public_debate_save_comment( $comment_id ) {
	if ( ( isset( $_POST['id_trecho_meta'] ) ) && ( $_POST['id_trecho_meta'] != '') )
	$id_trecho_meta = wp_filter_nohtml_kses($_POST['id_trecho_meta']);
	add_comment_meta( $comment_id, 'id_trecho_meta', $id_trecho_meta );

	if ( ( isset( $_POST['id_consulta_meta'] ) ) && ( $_POST['id_consulta_meta'] != '') )
	$id_consulta_meta = wp_filter_nohtml_kses($_POST['id_consulta_meta']);
	add_comment_meta( $comment_id, 'id_consulta_meta', $id_consulta_meta );
}


// Get the comments for each paragraph

function get_public_debate_comments( $id_trecho ){
$args = array(
	'meta_value' => $id_trecho
 );
 
$comments_query = new WP_Comment_Query;
$comments       = $comments_query->query( $args );
 
if( $comments ) :
	foreach( $comments as $comment ) :
		//echo $comment->comment_author;
		//print_r($comment);
		$gravatarURL = "http://1.gravatar.com/avatar/";
        $userGravatar = md5($comment->user_email);
        $gravatar = $gravatarURL.$userGravatar."?s=32";
		?>

		<div id="comment-<?php echo $comment->comment_date ?>" class="pd_part_comment_box">
			<span class="pd_part_comment_head"> <img alt="<?php echo $userName; ?>" src="<?php echo $gravatar; ?>"/> <a href="<?php echo $comment->comment_author_url ?>"><?php echo $comment->comment_author ?></a> disse:</span>
			<?php echo $comment->comment_content ?>
			<span class="pd_part_comment_date"> <?php echo $comment->comment_date ?></span>
		</div>
		<?php
	// div printando os comentarios do trecho
	echo "<br/>";
	endforeach;
endif;

}


?>
