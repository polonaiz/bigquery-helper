<?php


namespace BigqueryHelperCli\Command;


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\ServiceBuilder;
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
		$datasetAccessAsIsList = \json_decode(\file_get_contents($this->config['cliParams']['asIs']), true);
		$datasetAccessToBeList = \json_decode(\file_get_contents($this->config['cliParams']['toBe']), true);
		$outputFilePath = $this->config['cliParams']['output'] ?? 'php://stdout';

		$iterAsIs = new \ArrayIterator($datasetAccessAsIsList);
		$iterToBe = new \ArrayIterator($datasetAccessToBeList);
		while(
			($datasetAsIs = $iterAsIs->current()) &&
			($datasetToBe = $iterToBe->current())
		)
		{
			$datasetIdAsIs = $datasetAsIs['datasetId'];
			$datasetIdToBe = $datasetToBe['datasetId'];
			if($datasetAsIs !== $datasetToBe)
			{
				throw new \Exception('FAILURE: datasetId differ');
			}
			echo $datasetIdAsIs . PHP_EOL;
			echo $datasetIdToBe . PHP_EOL;

			$iterAsIs->next();
			$iterToBe->next();
		}

//		\file_put_contents(
//			$outputFilePath,
//			PrettyJson::getPrettyPrint(\json_encode($datasetAccessList)));
	}
}
