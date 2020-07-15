# bigquery-helper-cli

### Dataset Permission Manipulation

* concept
~~~
diff-dataset-access 
    (export-dataset-access) 
    (expect-dataset-access) 
| patch-dataset-access-diff
~~~

* export
~~~
    ./bin/bigquery-helper-cli export-dataset-access \
		--credential=credential.json \
		--output=datasetAccessCurrent.json
~~~

* expect
~~~
    ./bin/bigquery-helper-cli expect-dataset-access \
		--credential=credential.json \
		--config=dataset-access-control.json \
		--output=datasetAccessExpected.json
~~~

* diff:
~~~
	./bin/bigquery-helper-cli diff-dataset-access \
		--current=datasetAccessCurrent.json \
		--expected=datasetAccessExpected.json \
		--output=datasetAccessDiff.json
~~~

* patch:
~~~
	./bin/bigquery-helper-cli patch-dataset-access-diff \
		--credential=credential.json \
		--diff=datasetAccessDiff.json
~~~

* sample-dataset-access
~~~
{
	"datasetAccessConfigList": [
		{
			"datasetIdPattern": "/^DATASET_A_.+$/",
			"access": [
				{"role": "READER", "userByCustomGroup": "group-1"},
				{"role": "READER", "userByCustomGroup": "group-2"}
			]
		},
		{
			"datasetIdPattern": "/^DATASET_B_.+$/",
			"access": [
				{"role": "READER", "userByCustomGroup": "group-2"},
				{"role": "WRITER", "userByCustomGroup": "group-3"}
			]
		}
	],
	"customGroups": [
		{
			"customGroupId": "group-1",
			"members": [
				{"userByEmail": "tester-001@gmail.com"},
				{"userByEmail": "tester-002@gmail.com"}
			]
		},
		{
			"customGroupId": "group-2",
			"members": [
				{"userByEmail": "tester-003@gmail.com"},
				{"userByEmail": "tester-004@gmail.com"}
			]
		},
		{
			"customGroupId": "group-3",
			"members": [
				{"userByEmail": "tester-004@gmail.com"},
				{"userByEmail": "tester-005@gmail.com"}
			]
		}
	]
}
~~~
