<?php


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

	public function testListDatasetUsingDefaultCredential()
	{
		$serviceBuilder = new ServiceBuilder([
			'keyFilePath' => $this->getDefaultKeyFilePath(),
		]);
		$bigquery = $serviceBuilder->bigQuery();
		$datasetIterator = $bigquery->datasets();
		foreach ($datasetIterator as $datasetRef)
		{
			/** @var Google\Cloud\BigQuery\Dataset $datasetRef */
			$datasetRef->info();
		}
		$this->assertTrue(true);
	}
}
