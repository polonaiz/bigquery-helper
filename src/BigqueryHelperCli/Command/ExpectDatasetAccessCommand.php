<?php


namespace BigqueryHelperCli\Command;


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\ServiceBuilder;
use jDelta\PrettyJson;

class ExpectDatasetAccessCommand implements Command
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
		$projectId = $this->config['cliParams']['projectId'] ?? \getenv('BIGQUERY_HELPER_KEY_PROJECT_ID');
		$keyFilePath = $this->config['cliParams']['credential'] ?? \getenv('BIGQUERY_HELPER_KEY_FILE_PATH');
		$outputFilePath = $this->config['cliParams']['output'] ?? 'php://stdout';
		$configFilePath = $this->config['cliParams']['config'];
		$config = \json_decode(\file_get_contents($configFilePath), true);

		$serviceBuilder = new ServiceBuilder([
			'projectId' => $projectId,
			'keyFilePath' => $keyFilePath,
		]);
		$bigquery = $serviceBuilder->bigQuery();
		$datasetIterator = $bigquery->datasets();
		$datasetAccessList = [];
		foreach ($datasetIterator as $datasetRef)
		{
			/** @var Dataset $datasetRef */
			$datasetRefInfo = $datasetRef->info();
			$datasetId = $datasetRefInfo['datasetReference']['datasetId'];

			$datasetAccess = DatasetAccess::expandConfigDataset($datasetId, $config);
			$datasetAccessList[] = $datasetAccess;
		}

		\file_put_contents(
			$outputFilePath,
			PrettyJson::getPrettyPrint(\json_encode($datasetAccessList)));
	}
}
