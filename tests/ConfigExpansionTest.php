<?php


use BigqueryHelperCli\ArrayHandler;
use BigqueryHelperCli\DatasetAccess;
use PHPUnit\Framework\TestCase;

class ConfigExpansionTest extends TestCase
{
	public function testConfiguration()
	{
		foreach ([[
			'accessControlConfiguration' => __DIR__ . "/Asset/AccessControl/sample1-givenAccessControlConfiguration.json",
			'datasetIds' => __DIR__ . "/Asset/AccessControl/sample1-givenDatasetIds.json",
			'expectedAccessControl' => __DIR__ . "/Asset/AccessControl/sample1-expectedAccessControl.json"
		], [
			'accessControlConfiguration' => __DIR__ . "/Asset/AccessControl/sample2-givenAccessControlConfiguration.json",
			'datasetIds' => __DIR__ . "/Asset/AccessControl/sample2-givenDatasetIds.json",
			'expectedAccessControl' => __DIR__ . "/Asset/AccessControl/sample2-expectedAccessControl.json"
		], [
			'accessControlConfiguration' => __DIR__ . "/Asset/AccessControl/sample3-givenAccessControlConfiguration.json",
			'datasetIds' => __DIR__ . "/Asset/AccessControl/sample3-givenDatasetIds.json",
			'expectedAccessControl' => __DIR__ . "/Asset/AccessControl/sample3-expectedAccessControl.json"
		]] as $testset)
		{
			$accessControlConfiguration = \json_decode(\file_get_contents($testset['accessControlConfiguration']), true);
			$datasetIds = \json_decode(\file_get_contents($testset['datasetIds']), true);

			$result =
				$this->expandConfig($datasetIds, $accessControlConfiguration);

			$this->assertEquals(
				\json_encode(\json_decode(\file_get_contents(
					$testset['expectedAccessControl']), true), JSON_PRETTY_PRINT),
				\json_encode($result, JSON_PRETTY_PRINT));
		}
	}

	/**
	 * @param $datasetIds array
	 * @param $accessControlConfiguration array
	 * @return array
	 */
	public function expandConfig($datasetIds, $accessControlConfiguration)
	{
		$datasetAccessList = [];
		foreach ($datasetIds as $datasetId)
		{
			$datasetAccessList[] = [
				'datasetId' => $datasetId,
				'access' => DatasetAccess::expandConfigDataset(
					$datasetId,
					$accessControlConfiguration)];
		}
		return $datasetAccessList;
	}

	public function testArrayUnique()
	{
		//
		$data = [
			["role" => "READER", "userByEmail" => "tester-004@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-001@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-001@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-002@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-003@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-002@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-003@gmail.com"],
			["role" => "READER", "userByEmail" => "tester-001@gmail.com"],
		];

		(new ArrayHandler($data))
			->sort([DatasetAccess::class, 'accessEntryComparator'])
			->uniq([DatasetAccess::class, 'accessEntryComparator']);

		//
		$this->assertEquals(
			\json_encode([
				["role" => "READER", "userByEmail" => "tester-001@gmail.com"],
				["role" => "READER", "userByEmail" => "tester-002@gmail.com"],
				["role" => "READER", "userByEmail" => "tester-003@gmail.com"],
				["role" => "READER", "userByEmail" => "tester-004@gmail.com"],
			]),
			\json_encode($data));
	}

	public function testArrayHandle()
	{
		//
		$data = [2, 4, 3, 1, 2, 4];

		//
		$intComparator = fn($v1, $v2) => $v1 === $v2 ? 0 : ($v1 < $v2 ? -1 : +1);
		(new ArrayHandler($data))
			->sort($intComparator)
			->uniq($intComparator);

		//
		$this->assertEquals(
			\json_encode([1, 2, 3, 4]),
			\json_encode($data)
		);
	}
}

