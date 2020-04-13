<?php


namespace BigqueryHelperCli\Command;


class ExportCommand implements Command
{
	private array $config;

	public function __construct($config = [])
	{
		$this->config = $config;
	}

	/**
	 * @throws \Exception
	 */
	public function run()
	{
		var_dump($this->config['cliParams']);
	}
}