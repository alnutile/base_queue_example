## Base Queue Library


## Example

test/server/index shows an example use of this library

Register it with your dependency injection library

Setup the config for the SQS, Beanstalkd or IronQ you want to use

And then call to it

Some key features
  * Has a worker to manage the queue
  * Has a failed queue table to track fails
  * Trigger events to notify on fail
  * Trigger events to remove from queue on x fails
  * Process component to run multi threaded jobs


# Example in tests/server/index.php

You can run this with

~~~
php -S 127.0.0.1:8080
~~~

Then setup your .env file by copying env_example to .env with the right Amazon settings

Finally visit

http://127.0.0.1:8080/job_add to add a job

and

http://127.0.0.1:8080/get_next_job to get that job

This will add a class using the key I registered with Silex (Pimple is it's dependency injection component)
So when we later resolveAndFire the job we instantiate the class and have access to it and all
the events registered with $app.
Lastly the data we stored for the job is in the queue info as well (array with key class and data) that will
be the payload for the Class@fire method to use to process the job

Example of job in the queue

~~~
{"uuid":"b71227b0-64d2-49fd-a1d0-5f554e1a0c39","type":"test_base","data":"{\"serializerIdentifier\":\"Pekkis\\\\Queue\\\\Data\\\\BasicDataSerializer\",\"data\":\"a:2:{s:5:\\\"class\\\";s:19:\\\"example.queue.class\\\";s:4:\\\"data\\\";s:35:\\\"some random data here eg 1406567031\\\";}\"}"}
~~~
