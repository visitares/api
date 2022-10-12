<?php

namespace Visitares\JobQueue;

class JobStatus{

	const READY = 0;
	const ENQUEUED = 1;
	const RUNNING = 2;
	const DONE = 3;
	const FAILED = 4;
	const EXPIRED = 5;
	const ABORTED = 6;

}
