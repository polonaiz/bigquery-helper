<?php

namespace BigqueryHelperCli;

use jDelta\PrettyJson;
use PHPUnit\Framework\TestCase;

class DatasetAccessTest extends TestCase
{

	/**
	 * @throws \Exception
	 */
	public function testGrantAccess()
	{
		$datasetAccess = new DatasetAccess(\json_decode(
			<<<JSON
			{
				"datasetId": "TEST_DATASET",
				"access": [
					{"role": "WRITER","specialGroup": "projectWriters"},
					{"role": "OWNER","specialGroup": "projectOwners"},
					{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
					{"role": "READER","specialGroup": "projectReaders"},
					{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}}
				]
			}
			JSON, true
		));

		$datasetAccess->includeAccess([
			'role' => 'WRITER', 'userByEmail' => 'tester-001@gmail.com'
		]);

		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "WRITER","specialGroup": "projectWriters"},
						{"role": "OWNER","specialGroup": "projectOwners"},
						{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
						{"role": "READER","specialGroup": "projectReaders"},
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "WRITER","userByEmail": "tester-001@gmail.com"}
					] 
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->includeAccess([
			'role' => 'READER', 'userByEmail' => 'tester-002@gmail.com'
		]);

		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "WRITER","specialGroup": "projectWriters"},
						{"role": "OWNER","specialGroup": "projectOwners"},
						{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
						{"role": "READER","specialGroup": "projectReaders"},
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "WRITER","userByEmail": "tester-001@gmail.com"},
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->excludeAccessEntry([
			'role' => 'WRITER', 'userByEmail' => 'tester-001@gmail.com'
		]);

		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "WRITER","specialGroup": "projectWriters"},
						{"role": "OWNER","specialGroup": "projectOwners"},
						{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
						{"role": "READER","specialGroup": "projectReaders"},
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->excludeAccessEntry([
			'role' => 'WRITER', 'specialGroup' => 'projectWriters'
		]);
		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "OWNER","specialGroup": "projectOwners"},
						{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
						{"role": "READER","specialGroup": "projectReaders"},
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->excludeSpecialGroup();
		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "OWNER","userByEmail": "tester-001@gmail.com"},
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->excludeOwner();
		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"view": {"projectId": "test-project", "datasetId": "test-dataset", "tableId": "test-view"}},
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);

		$datasetAccess->excludeView();
		$this->assertEquals(
			PrettyJson::getPrettyPrint(
				<<<JSON
				{
					"datasetId": "TEST_DATASET",
					"access": [
						{"role": "READER","userByEmail": "tester-002@gmail.com"}
					]
				}
				JSON
			),
			PrettyJson::getPrettyPrint(\json_encode($datasetAccess->toArray()))
		);
	}
}
