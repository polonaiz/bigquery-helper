<?php


namespace BigqueryHelperCli\Command;


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\ServiceBuilder;
use jDelta\PrettyJson;

class ExportDatasetAccessCommand implements Command
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
		$outputFilePath = $this->config['cliParams']['output'] ?? 'php://stdout';

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

			$datasetInfo = $bigquery->dataset($datasetId)->info();
			$datasetAccess = (new DatasetAccess($datasetInfo['access']))
				->toArray();
			$datasetAccessList[] = [
				'datasetId' => $datasetInfo['datasetReference']['datasetId'],
				'access' => $datasetAccess
			];
		}

		\file_put_contents(
			$outputFilePath,
			PrettyJson::getPrettyPrint(\json_encode($datasetAccessList)));
	}
}
