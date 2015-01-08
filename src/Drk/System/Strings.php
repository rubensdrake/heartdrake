<?php
namespace Drk\System;
/**
* Classe responsavel por fazer a tradução
*/
class Strings
{

	static $strings =
		array(
			'pt-br' => /*Português Brasil \o/ */
					array(
						'class_not_found' => 'Classe não encontrada',
						'method_not_found' => 'Método não encontrado',
						'called_by' => 'Chamada por',
						'model_is_loaded' => 'Model carregado',
						'permission_denied' => 'Acesso Negado!',
						'app_not_configured' => 'Aplicativo n&atilde;o configurado!',
						'collection_not_defined' => 'Coleção não definida!',
					),
			'en' => /*English \o/ */
					array(
						'class_not_found' => 'Classe not found',
						'method_not_found' => 'Method not found',
						'called_by' => 'Called By',
						'model_is_loaded' => 'Model is loaded',
						'permission_denied' => 'Access Denied!',
						'app_not_configured' => 'App not configured!',
						'collection_not_defined' => 'Collection not defined!',
					)
		);

	static function get($string,$text = '')
	{
		$lang = defined('LANG') ? LANG : 'pt-br';
		
		return self::$strings[$lang][$string].' : '.$text;
	}
}

?>