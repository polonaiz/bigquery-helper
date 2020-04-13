<?php


namespace BigqueryHelperCli\Command;


interface Command
{
	/**
	 * @throws \Exception
	 */
	public function run();
}