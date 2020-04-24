<?php


namespace BigqueryHelperCli\Command;


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\BigQuery\Dataset;
use Google\Cloud\Core\ServiceBuilder;
use jDelta\PrettyJson;

class PatchDatasetAccessDiffCommand implements Command
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
		$diffFilePath = $this->config['cliParams']['diff'];
		$datasetAccessDiffList = \json_decode(\file_get_contents($diffFilePath), true);

		$serviceBuilder = new ServiceBuilder([
			'projectId' => $projectId,
			'keyFilePath' => $keyFilePath,
		]);
		$bigquery = $serviceBuilder->bigQuery();

		foreach ($datasetAccessDiffList as $datasetAccessDiff)
		{
			$datasetId = $datasetAccessDiff['datasetId'];
//			echo $datasetId . PHP_EOL;

			$datasetInfo = $bigquery->dataset($datasetId)->info();
//			echo PrettyJson::getPrettyPrint(
//					\json_encode([
//						'etag' => $datasetInfo['etag'],
//						'datasetId' => $datasetId,
//						'access' => $datasetInfo['access']
//					])) . PHP_EOL;

			$datasetAccess = new DatasetAccess([
				'datasetId' => $datasetId,
				'access' => $datasetInfo['access']]);

//			echo PrettyJson::getPrettyPrint(
//					\json_encode($datasetAccessDiff["accessPatchList"])) . PHP_EOL;
			foreach ($datasetAccessDiff["accessPatchList"] as $accessPatch)
			{
				if ($accessPatch['type'] === '-')
				{
					$datasetAccess->excludeAccessEntry([
						'role' => $accessPatch['role'], 'userByEmail' => $accessPatch['userByEmail']
					]);
				}
				elseif ($accessPatch['type'] === '+')
				{
					$datasetAccess->includeAccess([
						'role' => $accessPatch['role'], 'userByEmail' => $accessPatch['userByEmail']
					]);
				}
			}

//			echo PrettyJson::getPrettyPrint(
//					\json_encode([
//						'etag' => $datasetInfo['etag'],
//						'access' => $datasetAccess->toArray()['access']
//					])) . PHP_EOL;
			$updateInfo = $bigquery->dataset($datasetId)->update([
				'etag' => $datasetInfo['etag'],
				'access' => $datasetAccess->toArray()['access']
			]);
//			echo PrettyJson::getPrettyPrint(
//				\json_encode($updateInfo));
		}
	}
}
