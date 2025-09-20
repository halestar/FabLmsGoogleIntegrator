<?php

use App\Classes\Integrators\SecureVault;

$vault = app()->make(SecureVault::class);
return
[
	'google' =>
	[
		'client_id' => $vault->retrieve('google', 'client_id'),
		'client_secret' => $vault->retrieve('google', 'client_secret'),
		'redirect' => $vault->retrieve('google', 'redirect'),
	]
];