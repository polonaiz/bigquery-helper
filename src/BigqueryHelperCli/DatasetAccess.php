<?php


namespace BigqueryHelperCli;


// https://cloud.google.com/bigquery/docs/reference/rest/v2/datasets#resource

class DatasetAccess
{
	private array $data;

	public function __construct($data = [])
	{
		$this->data = $data;
	}

	/**
	 * @param array $entry
	 * @return DatasetAccess
	 * @throws \Exception
	 */
	public function includeAccess($entry)
	{
		$this->data[] = $entry;
		return $this;
	}

	public function excludeAccess($entry)
	{
		$data = [];
		foreach ($this->data as $datum)
		{
			if (\json_encode($datum) === \json_encode($entry))
			{
				continue;
			}
			$data[] = $datum;
		}
		$this->data = $data;
		return $this;
	}

	public function excludeSpecialGroup()
	{
		$data = [];
		foreach ($this->data as $datum)
		{
			if (isset($datum['specialGroup']))
			{
				continue;
			}
			$data[] = $datum;
		}
		$this->data = $data;
		return $this;
	}

	public function excludeOwner()
	{
		$data = [];
		foreach ($this->data as $datum)
		{
			if (isset($datum['role']) && $datum['role'] === 'OWNER')
			{
				continue;
			}
			$data[] = $datum;
		}
		$this->data = $data;
		return $this;
	}

	public function excludeView()
	{
		$data = [];
		foreach ($this->data as $datum)
		{
			if (isset($datum['view']))
			{
				continue;
			}
			$data[] = $datum;
		}
		$this->data = $data;
		return $this;
	}

	public
	function toArray()
	{
		return $this->data;
	}

	public static
	function accessEntryComparator($accessEntry1, $accessEntry2)
	{
		return \strcmp(\json_encode($accessEntry1), \json_encode($accessEntry2));
	}

	public static
	function expandConfigDataset($datasetId, $accessControlConfiguration)
	{
		$result = [];

		// build group dictionary
		$customGroupDict = [];
		foreach ($accessControlConfiguration['customGroups'] as $group)
		{
			$customGroupDict[$group['customGroupId']] = $group;
		}


		foreach ($accessControlConfiguration['datasetAccessConfigList'] as $datasetAccessConfig)
		{
			$matched = \preg_match($datasetAccessConfig['datasetIdPattern'], $datasetId);
			if ($matched === false || $matched === 0)
			{
				continue;
			}

			foreach ($datasetAccessConfig['access'] as $accessEntry)
			{
				$result = \array_merge($result, \array_map(
					function ($member) use ($accessEntry)
					{
						return ['role' => $accessEntry['role']] + ['userByEmail' => $member['userByEmail']];
					},
					$customGroupDict[$accessEntry['userByCustomGroup']]['members']
				));
			}
			unset($accessEntry);

			(new ArrayHandler($result))
				->sort([self::class, 'accessEntryComparator'])
				->uniq([self::class, 'accessEntryComparator']);
		}
		return $result;
	}
}