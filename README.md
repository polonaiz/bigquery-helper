# bigquery-helper-cli

### Dataset Permission Manipulation

* overview
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
