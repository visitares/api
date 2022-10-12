<?php

function getPdo(string $db){
	return new \PDO('mysql:host=127.0.0.1;dbname=db12298321-' . $db, 'dbu12298321', '*b%N@v!kRzEfX8K', [
  	\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
  	\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
	]);
}

function getInstances(\PDO $pdo){
	return $pdo->query('SELECT * FROM instance WHERE domain IS NOT NULL')->fetchAll();
}

function getInactiveInstances(\PDO $pdo){
	return $pdo->query('SELECT * FROM instance WHERE domain IS NULL')->fetchAll();
}

function findMediaByFilename(\PDO $pdo, string $filename){
	return $pdo->query(sprintf('SELECT * FROM media WHERE filename = "%s"', $filename))->fetch();
}

function getInstanceMediaFiles(string $token){
	return array_values(array_filter(glob(sprintf(__DIR__ .'/web/shared/user/%s/media/*.*', $token)), function(string $filename){
		/* */
    return strpos($filename, '-preview') === false;
  }));
}

/* (fix syntax color, lul) */
function getMasterMediaFiles(){
	return array_values(array_filter(glob(__DIR__ . "/web/shared/master/*/media/*.*"), function(string $filename){
		return strpos($filename, '-preview') === false;
	}));
}

$pdoVis = getPdo('visitares');

function checkFile(\PDO $pdoGlobal, \PDO $pdoLocal = null, string $filename = null){
	$basename = basename($filename);
	return (object)[
		'global' => findMediaByFilename($pdoGlobal, $basename),
		'local' => $pdoLocal ? findMediaByFilename($pdoLocal, $basename) : null,
	];
}

$s = (object)[
  'masterFiles' => 0,
  'masterFilesFoundGlobal' => 0,
  'masterFilesNotFoundGlobal' => 0,
  'files' => 0,
  'filesFound' => 0,
  'filesNotFound' => 0,
  'filesFoundLocal' => 0,
  'filesNotFoundLocal' => 0,
  'filesFoundGlobal'  => 0,
  'filesNotFoundGlobal' => 0,
];


foreach(getMasterMediaFiles() as $file){
	$s->masterFiles++;
	$result = checkFile($pdoVis, null, $file);
	$s->masterFiles++;
	if($result->global){
		$s->masterFilesFoundGlobal++;
	} else {
		$s->masterFilesNotFoundGlobal++;
	}
}

foreach(getInstances($pdoVis) as $inst){
	$pdoInst = getPdo($inst->token);
	foreach(getInstanceMediaFiles($inst->token) as $file){
		$result = checkFile($pdoVis, $pdoInst, $file);
  	$s->files++;
  	if($result->global){
	 		$s->filesFoundGlobal++;
  	} else {
  		$s->filesNotFoundGlobal++;
		}
    if($result->local){
      $s->filesFoundLocal++;
    } else {
      $s->filesNotFoundLocal++;
    }

		if($result->global || $result->local){
			$s->filesFound++;
		} else {
			$s->filesNotFound++;
		}

	}
}

$s->foundX = 0;
foreach(getInactiveInstances($pdoVis) as $inst){
	foreach(getInstanceMediaFiles($inst->token) as $file){
		$s->foundX;
	}
}

print_r($s);

exit;

foreach(getInstances($pdoVis) as $instance){

	printf("\n >>>>> INSTANCE %s / %s <<<<<\n", $instance->token, $instance->domain);
	$pdo = new \PDO('mysql:host=127.0.0.1;dbname=db12298321-' . $instance->token, 'dbu12298321', '*b%N@v!kRzEfX8K');

	foreach(array_merge(
		glob(sprintf(__DIR__ .'/web/shared/user/%s/media/*.*', $instance->token))
	) as $file){
		$s->files++;

		if(strpos($file, 'preview') !== false){
			$s->previews++;

			$masterFile = str_replace('-preview.jpg', '', $file);
			if(!file_exists($masterFile)){
				$s->previewsWithoutFile++;
			}

			continue;
		}

		# printf(" -> %s\n", $file);
		$pi = (object)pathinfo($file);
		# printf(" => %s\n", $pi->filename);

		$rowA = $pdo->query(sprintf('SELECT * FROM media WHERE filename = "%s"', basename($file)))->fetch(\PDO::FETCH_OBJ) ?? null;
		if(!$rowA){
			$s->notFoundLocal++;
			printf(" -> %s\n", $file);
			printf(" [!] NO LOCAL ENTRY FOUND\n");
		} else {
			$s->found++;
			$s->foundLocal++;
		}

		$rowB = $pdoVis->query(sprintf('SELECT * FROM media WHERE filename = "%s"', basename($file)))->fetch(\PDO::FETCH_OBJ) ?? null;
		if(!$rowB){
			$s->notFoundGlobal++;
		} else {
			$s->found++;
			$s->foundGlobal++;
		}

		if(!$rowA && !$rowB){
			$s->notFoundAtAll++;
		}

	}

	# /web/shared/user/tjyc/media

}

print_r($s);
