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
	public function grantAccess($entry)
	{
		$this->data[] = $entry;
		return $this;
	}

	public function revokeAccess($entry)
	{
		$data = [];
		foreach ($this->data as $idx => $datum)
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

	public function toArray()
	{
		return $this->data;
	}


}