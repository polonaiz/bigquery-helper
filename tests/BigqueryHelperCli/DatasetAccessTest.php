<?php

namespace BigqueryHelperCli;

use PHPUnit\Framework\TestCase;

class DatasetAccessTest extends TestCase
{

	/**
	 * @throws \Exception
	 */
	public function testGrantAccess()
	{
		$datasetAccess = new DatasetAccess(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"}
			]
			JSON, true
		));

		$datasetAccess->includeAccess([
			'role' => 'WRITER', 'userByEmail' => 'tester-001@gmail.com'
		]);

		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"},
				{"role": "WRITER","userByEmail": "tester-001@gmail.com"}
			]
			JSON, true)),
			\json_encode($datasetAccess->toArray())
		);

		$datasetAccess->includeAccess([
			'role' => 'READER', 'userByEmail' => 'tester-002@gmail.com'
		]);

		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"},
				{"role": "WRITER","userByEmail": "tester-001@gmail.com"},
				{"role": "READER","userByEmail": "tester-002@gmail.com"}
			]
			JSON, true)),
			\json_encode($datasetAccess->toArray())
		);

		$datasetAccess->excludeAccess([
			'role' => 'WRITER', 'userByEmail' => 'tester-001@gmail.com'
		]);

		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"},
				{"role": "READER","userByEmail": "tester-002@gmail.com"}
			]
			JSON, true), JSON_PRETTY_PRINT),
			\json_encode($datasetAccess->toArray(), JSON_PRETTY_PRINT)
		);

		$datasetAccess->excludeAccess([
			'role' => 'WRITER', 'specialGroup' => 'projectWriters'
		]);
		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"},
				{"role": "READER","userByEmail": "tester-002@gmail.com"}
			]
			JSON, true), JSON_PRETTY_PRINT),
			\json_encode($datasetAccess->toArray(), JSON_PRETTY_PRINT)
		);

		$datasetAccess->excludeSpecialGroup();
		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "READER","userByEmail": "tester-002@gmail.com"}
			]
			JSON, true), JSON_PRETTY_PRINT),
			\json_encode($datasetAccess->toArray(), JSON_PRETTY_PRINT)
		);
	}
}
