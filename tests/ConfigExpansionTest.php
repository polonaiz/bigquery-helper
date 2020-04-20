<?php


use PHPUnit\Framework\TestCase;

class ConfigExpansionTest extends TestCase
{
	public function testConfiguration()
	{
		$accessControlConfiguration =
			\json_decode(\file_get_contents(
				__DIR__ . "/Asset/AccessControl/sample1-givenAccessControlConfiguration.json"
			), true);
		$datasetIds =
			\json_decode(\file_get_contents(
				__DIR__ . "/Asset/AccessControl/sample1-givenDatasetIds.json"
			), true);

		$result =
			$this->expandConfig($accessControlConfiguration, $datasetIds);

		$this->assertEquals(
			\json_encode(\json_decode(\file_get_contents(
				__DIR__ . "/Asset/AccessControl/sample1-expectedAccessControl.json"), true), JSON_PRETTY_PRINT),
			\json_encode(
				$result, JSON_PRETTY_PRINT));
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
			}
		}

		return \array_values($datasetAccessDict);
	}
}
