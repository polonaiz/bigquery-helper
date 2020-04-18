<?php


use PHPUnit\Framework\TestCase;

class ConfigExpansionTest extends TestCase
{
	public function testConfiguration()
	{
		$configuration = \json_decode(\file_get_contents(
			__DIR__ . "/Asset/AccessControl/sample1-given-access-configs.json"
		), true);
		$datasetIdList = \json_decode(\file_get_contents(
			__DIR__ . "/Asset/AccessControl/sample1-given-dataset-id-list.json"
		), true);

		// build group dictionary
		$groupDict = [];
		foreach ($configuration['groups'] as $group)
		{
			$groupDict[$group['groupId']] = $group;
		}

		// expand dataset access config
		$datasetAccessDict = [];
		foreach ($configuration['datasetAccessConfigList'] as $datasetAccessConfig)
		{
			foreach ($datasetIdList as $datasetId)
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
			}
		}

		$this->assertEquals(
			\json_encode(\json_decode(\file_get_contents(
				__DIR__ . "/Asset/AccessControl/sample1-expected-dataset-access.json"), true), JSON_PRETTY_PRINT),
			\json_encode(\array_values($datasetAccessDict), JSON_PRETTY_PRINT)
		);
	}
}
