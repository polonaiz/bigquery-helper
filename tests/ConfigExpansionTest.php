<?php


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
				$this->expandConfig($accessControlConfiguration, $datasetIds);

			$this->assertEquals(
				\json_encode(\json_decode(\file_get_contents(
					$testset['expectedAccessControl']), true), JSON_PRETTY_PRINT),
				\json_encode($result, JSON_PRETTY_PRINT));
		}
	}

	/**
	 * @param $accessControlConfiguration array
	 * @param $datasetIds array
	 * @return array
	 */
	public function expandConfig($accessControlConfiguration, $datasetIds)
	{
		// build group dictionary
		$groupDict = [];
		foreach ($accessControlConfiguration['groups'] as $group)
		{
			$groupDict[$group['groupId']] = $group;
		}

		// expand dataset access config
		$datasetAccessDict = [];
		foreach ($accessControlConfiguration['datasetAccessConfigList'] as $datasetAccessConfig)
		{
			foreach ($datasetIds as $datasetId)
			{
				$result = \preg_match($datasetAccessConfig['datasetIdPattern'], $datasetId);
				if ($result === false || $result === 0)
				{
					continue;
				}

				if (!isset($datasetAccessDict[$datasetId]))
				{
					$datasetAccessDict[$datasetId] = [
						'datasetId' => $datasetId,
						'access' => []
					];
				}

				foreach ($datasetAccessConfig['access'] as $accessEntry)
				{
					foreach ($groupDict[$accessEntry['groupId']]['members'] as $member)
					{
						$datasetAccessDict[$datasetId]['access'][] = ['role' => $accessEntry['role']] + $member;
					}
				}
				unset($accessEntry);

				(new ArrayHandler($datasetAccessDict[$datasetId]['access']))
					->sort([$this, 'accessEntryComparator'])
					->uniq([$this, 'accessEntryComparator']);
			}
		}

		return \array_values($datasetAccessDict);
	}

	public function accessEntryComparator($accessEntry1, $accessEntry2)
	{
		return \strcmp(\json_encode($accessEntry1), \json_encode($accessEntry2));
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
			->sort([$this, 'accessEntryComparator'])
			->uniq([$this, 'accessEntryComparator']);

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

class ArrayHandler
{
	private array $data;

	public function __construct(array &$data)
	{
		$this->data = &$data;
	}

	public function sort(callable $comparer)
	{
		\usort($this->data, $comparer);

		return $this;
	}

	public function uniq(callable $comparer)
	{
		$carry = \array_reduce(
			$this->data,
			function ($carry, $current) use (&$previous, $comparer)
			{
				if ($comparer($previous, $current) !== 0)
				{
					$carry[] = $current;
				}
				$previous = $current;
				return $carry;
			},
			$initialCarry = []
		);
		$this->data = $carry;

		return $this;
	}
}
