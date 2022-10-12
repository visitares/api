<?php

namespace Visitares\Service\Database;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Twig\Environment as Twig;

/**
 * Creates configuration files and runs migrations.
 *
 * @author Ricard Derheim <rderheim@derheim-software.de>
 */
class Migration{
	/**
	 * @var array
	 */
	protected $config = null;

	/**
	 * @var Twig
	 */
	protected $twig = null;

	/**
	 * Find more informationen about usage of `PhinxApplication` at:
	 * https://github.com/robmorgan/phinx/issues/180
	 *
	 * @var PhinxApplication
	 */
	protected $phinx = null;

	/**
	 * @param array $migrationConfig
	 * @param Twig $twig
	 * @param PhinxApplication $phinx
	 */
	public function __construct(
		$migrationConfig,
		Twig $twig,
		PhinxApplication $phinx
	){
		$this->config = $migrationConfig;
		$this->twig = $twig;
		$this->phinx = $phinx;
	}

	/**
	 * @param  string $token
	 * @return string
	 */
	protected function getConfigLocation($token){
		return sprintf('%s/config/%s%s.php', $this->config['dir'], $this->config['prefix'], $token);
	}

	/**
	 * @param  string $token
	 * @return boolean
	 */
	public function createConfig($token){
		$content = $this->twig->render('migration/config.php', [
			'token' => $token
		]);
		file_put_contents($this->getConfigLocation($token), $content);
		return true;
	}

	/**
	 * @param  string $token
	 * @return boolean
	 */
	public function deleteConfig($token){
		$location = $this->getConfigLocation($token);
		if(file_exists($location)){
			unlink($location);
		}
		return true;
	}

	/**
	 * @param  string $token
	 * @return integer
	 */
	public function run($token){
		$migrate = $this->phinx->find('migrate');
		$arguments = [
			'command' => 'migrate',
			'--configuration' => $this->getConfigLocation($token)
		];
		$in = new ArrayInput($arguments);
		$out = new NullOutput;
		$exitCode = $migrate->run($in, $out);
		return $exitCode;
	}

	/**
	 * @param  string $token
	 * @return string
	 */
	public function rollback($token){
		$rollback = $this->phinx->find('rollback');
		$arguments = [
			'command' => 'rollback',
			'--configuration' => $this->getConfigLocation($token),
			'--target' => 0
		];
		$in = new ArrayInput($arguments);
		$out = new NullOutput;
		$exitCode = $rollback->run($in, $out);
		return $exitCode;
	}
}