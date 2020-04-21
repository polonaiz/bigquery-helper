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
			if(isset($datum['specialGroup']))
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