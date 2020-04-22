<?php


namespace BigqueryHelperCli;


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
