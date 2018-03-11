<?php
require 'simple_html_dom.php';
$url = 'http://www.teste.com';
$base = parse_url($url)['host'];

$array_link = array();//vetor temporario onde os links entram e saem conforme sao verificados
$array_link_totais = array();//vetor com todos os links encontrados
$saidas = array();//saidas

$ext_exc = array("jpg","png","gif","doc","docx","zip","rar","xls","xlsx","ppt","pptx",'pdf');//vetor com extençoes para dispensar leitura

function busca($url){
	global $array_link;
	global $array_link_totais;
	global $base;
	global $ext_exc;
	$ext_temp = explode(".", $url); //verifica se nao é uma url de download pelo array ext_exc

	if(in_array($ext_temp[count($ext_temp)-1], $ext_exc)){
		array_shift($array_link);
		return null;
	}

	$html = file_get_html( $url ); // abre o html
	
	if($html == false){//verifica se retornou direito o html
		array_shift($array_link);
		return null;
	}
	

	localizar_conteudo($html);//chama a busca do desejo


	try{ //procura os links
		$posts = $html->find('a');
	}catch(Exception $e){
		array_shift($array_link);	
		return null;
	}
	
	foreach( $posts as $post ){//toda todos os links
		
		$url_busca = $post->attr['href'];

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

	
	 
	 array_shift($array_link);
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

function localizar_conteudo($html){// aqui vem a busca de informações do conteudo.
	global $saidas;
	//aqui vc coloca a busca que vai ser feita realmente na pagina
	$titles = $html->find('h1[class=entry-title]');
	foreach( $titles as $title ){
		$saidas[] = $title->innertext;
	}
	//fecha busca//a busca pelo conteudo real
}


$antes = date("Y-m-d H:i:s");

$array_link[] = $url;
$array_link_totais[] = $url;
busca($url);

print_r($array_link_totais);//impressao dos links encontrados
print_r($saidas);//coisas encontradas.

//tempo de execução.
$depois = date("Y-m-d H:i:s");
print("\n\n");
echo $antes . "  " . $depois;