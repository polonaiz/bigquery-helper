<?php


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\Core\ServiceBuilder;
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
	private function getDefaultProjectId()
	{
		return \getenv('BIGQUERY_HELPER_KEY_PROJECT_ID');
	}

	private function getDefaultKeyFilePath()
	{
		return \getenv('BIGQUERY_HELPER_KEY_FILE_PATH');
	}

	public function testDiscoverDefaultCredential()
	{
		$projectId = $this->getDefaultProjectId();
		$this->assertNotFalse($projectId);

		$keyFilePath = $this->getDefaultKeyFilePath();
		$this->assertNotFalse($keyFilePath);
		$this->assertTrue(\file_exists($keyFilePath));
	}

	/**
	 * @throws Exception
	 */
	public function testPermission()
	{
		$serviceBuilder = new ServiceBuilder([
			'keyFilePath' => $this->getDefaultKeyFilePath(),
		]);
		$bigquery = $serviceBuilder->bigQuery();
		$datasetId = 'zzz_test__BIGQUERY_HELPER';
		$testAccount = 'polonaiz@gmail.com';

		// ensure cleanup
		if ($bigquery->dataset($datasetId)->exists())
		{
			$bigquery->dataset($datasetId)->delete();
		}

		// test dataset creation
		$bigquery->createDataset($datasetId);

		// test dataset info
		$datasetInfo = $bigquery->dataset($datasetId)->info();
		$this->assertEquals($datasetId, $datasetInfo['datasetReference']['datasetId']);
		echo \json_encode($datasetInfo, JSON_PRETTY_PRINT) . PHP_EOL;

		// grant
		$grantMetaData =
			[
				'etag' => $datasetInfo['etag'],
				'access' => (new DatasetAccess($datasetInfo['access']))
					->grantAccess(["role" => "WRITER", "userByEmail" => $testAccount])
					->toArray()
			];
		echo \json_encode(['grantMetaData' => $grantMetaData], JSON_PRETTY_PRINT) . PHP_EOL;
		$updatedDatasetInfo = $bigquery->dataset($datasetId)->update($grantMetaData);
		echo \json_encode(['grantUpdateResult' => $updatedDatasetInfo], JSON_PRETTY_PRINT) . PHP_EOL;

		// revoke
		$reloadedDatasetInfo = $bigquery->dataset($datasetId)->info();
		echo \json_encode(['reloadedDatasetInfo' => $reloadedDatasetInfo]) . PHP_EOL;
		$revokeMetaData =
			[
				'etag' => $reloadedDatasetInfo['etag'],
				'access' => (new DatasetAccess($reloadedDatasetInfo['access']))
					->revokeAccess(["role" => "WRITER", "userByEmail" => $testAccount])
					->toArray()
			];
		echo \json_encode(['revokeMetaData' => $revokeMetaData], JSON_PRETTY_PRINT) . PHP_EOL;
		$revokedDatasetInfo = $bigquery->dataset($datasetId)->update($revokeMetaData);
		echo \json_encode(['revokedDatasetInfo' => $revokedDatasetInfo], JSON_PRETTY_PRINT) . PHP_EOL;

		// final state
		$reloadedDatasetInfo = $bigquery->dataset($datasetId)->info();
		echo \json_encode(['reloadedDatasetInfo' => $reloadedDatasetInfo], JSON_PRETTY_PRINT) . PHP_EOL;

		// test dataset deletion
		$bigquery->dataset($datasetId)->delete();

		$this->assertTrue(true);
	}
}
