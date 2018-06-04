<?php

require_once "./FfmpegEffects.php";

$effect = new FfmpegEffects();

##########################
# Set enviroment FONTCONFIG_FILE . This require for addTextAss effect
$basedir = dirname(__FILE__);
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    putenv("FONTCONFIG_FILE=$basedir\\fonts.conf"); # for windows
}

#$tempDir=sys_get_temp_dir();
#$tempDir="$basedir/";
$tempDir = "./";

$options = getopt('j:o:');
$jsonFile = isset($options['j']) ? $options['j'] : false;
$outputJson = isset($options['o']) ? $options['o'] : false;

if (!$outputJson) {
    help("Need parameter json file ( -o output.json ) ");
}
if (!$jsonFile) {
    help("Need parameter json file ( -j file.json ) ");
}
if (!file_exists($jsonFile)) {
    help("File $jsonFile do not exist");
}
$string = file_get_contents($jsonFile);
$params = json_decode($string, true);
if (!$params) {
    help("Cannot decode json from $jsonFile");
}

# we must split with slow speed and hi quality
$effect->setVideoOutputSettings(
    array(
        'format' => 'mpegts',
        'preset' => 'veryfast',
        'crf' => 23,
    )
);

$input = $params['info']['input'];
$outputPrefix = $params['info']['outputPrefix'];
$outputHeight = $params['info']['outputHeight'];
$outputWidth = $params['info']['outputWidth'];

$returnArray = array();
$returnArray['video'] = array();
$returnArray['audio'] = $params['audio'];

foreach ($params['timeline'] as $timeline) {
    $videoFile = $timeline['filename'];
    $tempFile = time() . rand(10000, 99999);

    if (isset($timeline['animation']['effect']) && $timeline['animation']['effect'] === "textSkewShadowAnimation") {
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = $tempFile . ".ass";

        $tmpVideoFile = $tempFile . ".ts";
        $cmd = $effect->textSkewShadowAnimation(
            $videoFile,
            $assFile,
            $timeline['animation'],
            $tmpVideoFile,
            $outputWidth,
            $outputHeight
        );

        if (!$cmd) {
            $effect->writeToLog("Someting wrong while add animation : $cmd ." . $effect->getLastError());
            array_push($returnArray['video'], array("filename" => $videoFile));
            continue;
            //exit(1);
        }
        if (!$effect->doExec($cmd)) {
            $effect->writeToLog("Someting wrong with command: $cmd");
            exit(1);
        }

        @unlink($assFile);
        array_push($returnArray['video'], array("filename" => $tmpVideoFile));
        continue;
    }    

    if (isset($timeline['animation']['effect']) && $timeline['animation']['effect'] === "moneyCounterAnimation") {
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = $tempFile . ".ass";

        $tmpVideoFile = $tempFile . ".ts";
        $cmd = $effect->moneyCounterAnimation(
            $videoFile,
            $assFile,
            $timeline['animation'],
            $tmpVideoFile,
            $outputWidth,
            $outputHeight
        );

        if (!$cmd) {
            $effect->writeToLog("Someting wrong while add animation : $cmd ." . $effect->getLastError());
            array_push($returnArray['video'], array("filename" => $videoFile));
            continue;
            //exit(1);
        }
        if (!$effect->doExec($cmd)) {
            $effect->writeToLog("Someting wrong with command: $cmd");
            exit(1);
        }

        @unlink($assFile);
        array_push($returnArray['video'], array("filename" => $tmpVideoFile));
        continue;
    }

//  text animation effect

    if (isset($timeline['animation']['effect']) && $timeline['animation']['effect'] === "textAndImageAnimation") {
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = $tempFile . ".ass";

        $tmpVideoFile = $tempFile . ".ts";
        $cmd = $effect->textAndImageAnimation(
            $videoFile,
            $assFile,
            $timeline['animation'],
            $tmpVideoFile,
            $outputWidth,
            $outputHeight
        );

        if (!$cmd) {
            $effect->writeToLog("Someting wrong while add animation : $cmd ." . $effect->getLastError());
            array_push($returnArray['video'], array("filename" => $videoFile));
            continue;
            //exit(1);
        }
        if (!$effect->doExec($cmd)) {
            $effect->writeToLog("Someting wrong with command: $cmd");
            exit(1);
        }

        @unlink($assFile);
        array_push($returnArray['video'], array("filename" => $tmpVideoFile));
        continue;
    }

//  text animation effect

    if (isset($timeline['animation']['effect']) && $timeline['animation']['effect'] === "textAnimation") {
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = $tempFile . ".ass";

        $tmpVideoFile = $tempFile . ".ts";
        $cmd = $effect->textAnimation(
            $videoFile,
            $assFile,
            $timeline['animation'],
            $tmpVideoFile,
            $outputWidth,
            $outputHeight
        );

        if (!$cmd) {
            $effect->writeToLog("Someting wrong while add animation : $cmd ." . $effect->getLastError());
            array_push($returnArray['video'], array("filename" => $videoFile));
            continue;
            //exit(1);
        }
        if (!$effect->doExec($cmd)) {
            $effect->writeToLog("Someting wrong with command: $cmd");
            exit(1);
        }

        @unlink($assFile);
        array_push($returnArray['video'], array("filename" => $tmpVideoFile));
        continue;
    }
    array_push($returnArray['video'], array("filename" => $videoFile));
}
file_put_contents($outputJson, json_encode($returnArray, JSON_PRETTY_PRINT));

exit(0);

function help($msg)
{
    fwrite(STDERR,
        "$msg
    Generate ass files for animation


	Usage:$0 -j file.json -o animation.json
	\n");
    exit(-1);
}
