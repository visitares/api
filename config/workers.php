<?php

return[

	'send-mail' => (object)[
		'limit' => 30,
		'worker' => \Visitares\JobQueue\Workers\SendMailWorker::class
	],
	'notify-post-subs' => (object)[
		'limit' => 0,
		'worker' => \Visitares\JobQueue\Workers\NotifyPostSubsWorker::class
	],

];
