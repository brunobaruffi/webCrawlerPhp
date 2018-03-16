<?php
require 'simple_html_dom.php';
$url = 'https://www.teste.com.br/';
$base = parse_url($url)['host'];

$array_link_totais = array();//vetor com todos os links encontrados
$saidas = array();//saidas
$saida_url = array();//saida url
$ext_exc = array("jpg","png","gif","doc","docx","zip","rar","xls","xlsx","ppt","pptx",'pdf');//vetor com extençoes para dispensar leitura

//modificação para que o try-catch pegue as notificações.
set_error_handler('exceptions_error_handler');
function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}



function busca($url){
	$array_link = array();
	global $array_link_totais;
	global $base;
	global $ext_exc;
	
	try{//se for 404 tenque anular.
		$html = file_get_html( $url ); // abre o html
	}catch(Exception $e){
		return null;
	}

	if($html == false){//verifica se retornou direito o html
		return null;
	}
	

	localizar_conteudo($html,$url);//chama a busca do desejo


	try{ //procura os links
		$posts = $html->find('a');
	}catch(Exception $e){
		return null;
	}
	
	foreach( $posts as $post ){//toda todos os links
		//print ($url_busca . " >>");
		try{	
			$url_busca = $post->attr['href'];
		}catch(Exception $e){
			continue;
		}
		$ext_temp = explode(".", $url_busca); //verifica se nao é uma url de download pelo array ext_exc
		if(in_array($ext_temp[count($ext_temp)-1], $ext_exc)){
			continue;
		}


		$temp_url = explode("#", $url_busca);// tirar ancoragem
		$url_busca = $temp_url[0];
		
		if(($url_busca != "")&&($url_busca != "#")){
			if(substr($url_busca,0,7) != "mailto:"){//buscas para retirada dos links inuteis.
				$url_busca = concerto_url($url,$url_busca);
				$base_t = parse_url($url_busca)['host'];
				if($base == $base_t){//verifica se esta no mesmo dominio.
					if(!in_array($url_busca, $array_link_totais)){//verifica se o link ja n esta no array de saida do link.
						$array_link[] = $url_busca;
						$array_link_totais[] = $url_busca;		
					}	
				}
			}	
		}
	}

	foreach ($array_link as $links) {
	 	busca($links);
	 }
}

function concerto_url($url,$l){ // a url da rodada e os links encontradaos na url em questao
	if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
	} else if (substr($l, 0, 2) == "//") {
		$l = parse_url($url)["scheme"].":".$l;
	} else if (substr($l, 0, 2) == "./") {
		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
	} else if (substr($l, 0, 1) == "#") {
		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
	} else if (substr($l, 0, 3) == "../") {
		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
	} else if (substr($l, 0, 11) == "javascript:") {
		
	} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
		$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
	}
	return $l;
}

function localizar_conteudo($html,$url){// aqui vem a busca de informações do conteudo.
	global $saidas;
	//aqui vc coloca a busca que vai ser feita realmente na pagina
	//aqui voce pode especificar quandos parametros de busca quizer seguindo este modelo.
	//Ex: o span deve estar dentro do h1.	
	$divs = $html->find('h1[class=single-post-title] span[class=post-title]');
	foreach( $divs as $div ){
		$temp = trim($div->innertext);
		if(!in_array($temp, $saidas)){
			$saidas[] = $temp;
			$saida_url[] = $url;
		}
	}
}


$antes = date("Y-m-d H:i:s");

$array_link_totais[] = $url;
busca($url);

print_r($array_link_totais);//impressao dos links encontrados
print_r($saidas);//coisas encontradas.

//tempo de execução.
$depois = date("Y-m-d H:i:s");
print("\n\n");
print("Antes:".$antes . "  Depois:" . $depois);
print("\n\n");
