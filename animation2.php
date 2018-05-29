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

$returnArray = array();
$returnArray['video'] = array();
$returnArray['audio'] = $params['audio'];

foreach ($params['timeline'] as $timeline) {
#echo var_dump( $sub_edited->subtitles );
    #$assFile = "${outputPrefix}_${start}_${end}.ass";
    $videoFile = $timeline['filename'];
    if (isset($timeline['animation']['effect']) && $timeline['animation']['effect'] === "textAnimation") {
        $tempFile = tempnam($tempDir, 'Tux');
        $tempFile = "tmp_file";
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = $tempFile . ".ass";

        if (!$effect->prepareSubtitles($timeline['animation']['text'], $assFile)) {
            $effect->writeToLog("Cannot save ass subtitles file $assFile :" . $effect->getLastError());
            continue;
        }

        # todo
        #$tmpVideoFile = "${outputPrefix}_${start}_${end}_animation.ts";
        $tmpVideoFile = $tempFile . ".ts";

        $cmd = $effect->textAnimation(
            $videoFile,
            $assFile,
            $tmpVideoFile
        );

        if (!$cmd) {
            $effect->writeToLog("Someting wrong while add animation : $cmd ." . $effect->getLastError());
            exit(1);
        }
        if (!$effect->doExec($cmd)) {
            $effect->writeToLog("Someting wrong with command: $cmd");
            exit(1);
        }

        @unlink($tempFile);
        @unlink($assFile);
        array_push($returnArray['video'], array("filename" => $tmpVideoFile));
        continue;
    }
    array_push($returnArray['video'], array("filename" => $videoFile));
    #array_push($returnArray['timeline'], $timeline);
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
