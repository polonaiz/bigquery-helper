<?php


use BigqueryHelperCli\DatasetAccess;
use Google\Cloud\Core\ServiceBuilder;
use PHPUnit\Framework\TestCase;

class BigqueryApiTest extends TestCase
{
	private function getDefaultProjectId()
	{
		return \getenv('BIGQUERY_HELPER_KEY_PROJECT_ID');
	}

	private function getDefaultKeyFilePath()
	{
		return \getenv('BIGQUERY_HELPER_KEY_FILE_PATH');
	}

	public function testObtainDefaultCredential()
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
	public function testPermissionChangeUsingBigqueryApi()
	{
		$this->markTestSkipped();

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

		// grant
		$currentDatasetInfo = $bigquery->dataset($datasetId)->info();
		$grantUpdateResult = $bigquery->dataset($datasetId)->update($grantMetaData = [
			'etag' => $currentDatasetInfo['etag'],
			'access' => (new DatasetAccess($currentDatasetInfo['access']))
				->includeAccess(["role" => "WRITER", "userByEmail" => $testAccount])
				->toArray()
		]);
		echo \json_encode(['grantUpdateResult' => $grantUpdateResult], JSON_PRETTY_PRINT) . PHP_EOL;

		// revoke
		$currentDatasetInfo = $bigquery->dataset($datasetId)->info();
		$revokeUpdateResult = $bigquery->dataset($datasetId)->update($revokeMetaData = [
			'etag' => $currentDatasetInfo['etag'],
			'access' => (new DatasetAccess($currentDatasetInfo['access']))
				->excludeAccessEntry(["role" => "WRITER", "userByEmail" => $testAccount])
				->toArray()
		]);
		echo \json_encode(['revokeUpdateResult' => $revokeUpdateResult], JSON_PRETTY_PRINT) . PHP_EOL;

		// final state
		$currentDatasetInfo = $bigquery->dataset($datasetId)->info();
		echo \json_encode(['currentDatasetInfo' => $currentDatasetInfo], JSON_PRETTY_PRINT) . PHP_EOL;

		// test dataset deletion
		$bigquery->dataset($datasetId)->delete();

		$this->assertTrue(true);
	}
}
