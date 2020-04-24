<?php


namespace BigqueryHelperCli\Command;


use BigqueryHelperCli\DatasetAccess;
use jDelta\PrettyJson;

class DiffDatasetAccessCommand implements Command
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
		$datasetAccessListCurrent = \json_decode(\file_get_contents($this->config['cliParams']['current']), true);
		$datasetAccessListExpected = \json_decode(\file_get_contents($this->config['cliParams']['expected']), true);
		$outputFilePath = $this->config['cliParams']['output'] ?? 'php://stdout';

		$DatasetAccessDiffListData = [];
		$iterCurrent = new \ArrayIterator($datasetAccessListCurrent);
		$iterExpected = new \ArrayIterator($datasetAccessListExpected);
		while (
			($datasetAccessCurrent = $iterCurrent->current()) &&
			($datasetAccessExpected = $iterExpected->current())
		)
		{
			$DatasetAccessDiffData =
				DatasetAccess::diff($datasetAccessCurrent, $datasetAccessExpected);
			if(count($DatasetAccessDiffData['accessPatchList']) > 0)
			{
				$DatasetAccessDiffListData[] = $DatasetAccessDiffData;
			}

			$iterCurrent->next();
			$iterExpected->next();
		}

		\file_put_contents(
			$outputFilePath,
			PrettyJson::getPrettyPrint(\json_encode($DatasetAccessDiffListData)) . PHP_EOL);
	}
}
