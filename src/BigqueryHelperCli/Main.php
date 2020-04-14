<?php


namespace BigqueryHelperCli;

use BigqueryHelperCli\Command\CommandFactory;
use Phalcon\Cop\Parser;
use ScratchPad\Exception\Exception;
use ScratchPad\Logger\CompositeLogger;
use ScratchPad\Logger\ConsoleLogger;
use ScratchPad\Logger\Logger;
use ScratchPad\Throwable;

class Main
{
	const PROGRAM_NAME = 'bigquery-helper-cli';
	/**
	 * @throws \Throwable
	 */
	public static function main()
	{
		try
		{
			self::env();

			global $argv;
			$cliParser = new Parser();
			$cliParams = $cliParser->parse($argv);

			CommandFactory::createCommand($cliParams)->run();
			return;
		}
		catch (\Throwable $t)
		{
			echo \json_encode([
					'type' => 'exception',
					'message' => $t->getMessage(),
					'trace' => Throwable::getTraceSafe($t),
				], JSON_PRETTY_PRINT) . PHP_EOL;

			throw $t;
		}
	}

	public static function env()
	{
		ini_set('memory_limit', '1024M');
		ini_set('serialize_precision', -1);
		date_default_timezone_set('Asia/Seoul');
		Exception::convertNonFatalErrorToException();

		// configure logger
		Logger::setLogger(new CompositeLogger(
			[
				'defaults' =>
					[
						'timestamp' => CompositeLogger::getTimeStamper(),
						'host' => gethostname(),
						'program' => self::PROGRAM_NAME,
						'pid' => getmypid()
					],
				'loggerFilterPairs' =>
					[
						[
							'logger' => new ConsoleLogger(['appendNewLine' => 1]),
							'filter' => CompositeLogger::getSelectorAll()
						],
					]
			]));
	}
}
