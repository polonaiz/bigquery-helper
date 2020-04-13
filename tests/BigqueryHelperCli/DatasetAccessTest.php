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

		$datasetAccess->grantAccess([
			'role' => 'WRITER', 'userByEmail' => 'polonaiz@gmail.com'
		]);

		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"},
				{"role": "WRITER","userByEmail": "polonaiz@gmail.com"}
			]
			JSON, true)),
			\json_encode($datasetAccess->toArray())
		);

		$datasetAccess->revokeAccess([
			'role' => 'WRITER', 'userByEmail' => 'polonaiz@gmail.com'
		]);

		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "WRITER","specialGroup": "projectWriters"},
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"}
			]
			JSON, true), JSON_PRETTY_PRINT),
			\json_encode($datasetAccess->toArray(), JSON_PRETTY_PRINT)
		);

		$datasetAccess->revokeAccess([
			'role' => 'WRITER', 'specialGroup' => 'projectWriters'
		]);
		$this->assertEquals(\json_encode(\json_decode(<<<JSON
			[
				{"role": "OWNER","specialGroup": "projectOwners"},
				{"role": "READER","specialGroup": "projectReaders"}
			]
			JSON, true), JSON_PRETTY_PRINT),
			\json_encode($datasetAccess->toArray(), JSON_PRETTY_PRINT)
		);
	}
}
