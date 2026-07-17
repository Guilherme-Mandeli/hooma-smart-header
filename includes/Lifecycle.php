<?php

namespace HoomaModules\HoomaSmartHeader;

use Hooma_Module_Lifecycle_Interface;
use Hooma;

defined('HOOMA_PATH') || exit;

class Lifecycle implements Hooma_Module_Lifecycle_Interface
{

	/**
	 * Instalação
	 * 
	 * Executado uma única vez assim que o módulo é instalado com sucesso.
	 * Ideal para: Criar tabelas no banco de dados, definir opções padrão, criar roles.
	 *
	 * @param array $manifest Dados del manifesto del módulo (versión, slug, etc).
	 * @return void
	 */
	public static function install(array $manifest)
	{
		Hooma::logger()->info(sprintf('Modulo %s instalado con éxito en la versión %s', $manifest['slug'], $manifest['version']));
	}

	/**
	 * Atualização
	 * 
	 * Executado quando uma nova versão do módulo substitui uma antiga.
	 * Ideal para: Migração de dados de banco, atualização de estrutura de tabelas.
	 *
	 * @param array $old_manifest Dados da versão antiga (antes del update).
	 * @param array $new_manifest Dados da nova versão (atual).
	 * @return void
	 */
	public static function update(array $old_manifest, array $new_manifest)
	{

	}

	/**
	 * Desinstalação
	 * 
	 * Executado quando o módulo é removido via painel.
	 * Ideal para: Limpeza de dados, remoção de tabelas e opções (Cleanup).
	 *
	 * @param array $manifest Dados do módulo sendo removido.
	 * @return void
	 */
	public static function uninstall(array $manifest)
	{
		// Limpieza completa de opciones y cachés del módulo usando el Core de Hooma
		Hooma::settings()->delete_namespace('hooma_smart_header');
		Hooma::cache()->delete('hooma_sh_header_images_cache');
		Hooma::cache()->delete('hooma_sh_last_run_strategy');
		Hooma::scheduler()->unschedule('hooma_smart_header.warm_cache');
	}
}
