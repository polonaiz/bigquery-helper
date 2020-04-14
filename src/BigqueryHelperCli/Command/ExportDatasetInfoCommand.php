<?php


namespace BigqueryHelperCli\Command;


use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\ServiceBuilder;

class ExportDatasetInfoCommand implements Command
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
		$projectId = \getenv('BIGQUERY_HELPER_KEY_PROJECT_ID');
		$keyFilePath = \getenv('BIGQUERY_HELPER_KEY_FILE_PATH');

		$serviceBuilder = new ServiceBuilder([
			'projectId' => $projectId,
			'keyFilePath' => $keyFilePath,
		]);
		$bigquery = $serviceBuilder->bigQuery();
		$datasetIterator = $bigquery->datasets();
		foreach ($datasetIterator as $datasetRef)
		{
			/** @var Dataset $datasetRef */
			$datasetRefInfo = $datasetRef->info();
			$datasetId = $datasetRefInfo['datasetReference']['datasetId'];

			$datasetInfo = $bigquery->dataset($datasetId)->info();
			echo \json_encode($datasetInfo), PHP_EOL;
		}
	}
}