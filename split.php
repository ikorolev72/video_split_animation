<?php

# This script split video to parts
# korolev-ia@yandex.ru
#

require_once "./FfmpegEffects.php";
##########################
# Set enviroment FONTCONFIG_FILE . This require for addTextAss effect
$basedir = dirname(__FILE__);

$options = getopt('j:o:s:');

$jsonFile = isset($options['j']) ? $options['j'] : false;
$outputJson = isset($options['o']) ? $options['o'] : false;

if (!$jsonFile) {
    help("Need parameter json file ( -j file.json ) ");
}
if (!$outputJson) {
    help("Need parameter json file ( -o output.json ) ");
}

if (!file_exists($jsonFile)) {
    help("File $jsonFile do not exist");
}

$string = file_get_contents($jsonFile);
$params = json_decode($string, true);
if (!$params) {
    help("Cannot decode json from $jsonFile");
}

#$tempDir=sys_get_temp_dir();
#$tempDir="$basedir/";
$tempDir = "./";

# new instance for FfmpegEffects
$effect = new FfmpegEffects();

$effect->setGeneralSettings(
    array(
        'ffmpegLogLevel' => 'info',
        'showCommand' => false,
    )
);
#echo "General settings:";
#echo var_dump($effect->getGeneralSettings());

# set ffmpeg new audio output settings
$effect->setAudioOutputSettings(
    array(
        'bitrate' => '192k',
        'codec' => 'aac',
    )
);
#echo "New settings for output audio ffmpeg:";
#echo var_dump($effect->getAudioOutputSettings());

# we must split with slow speed and hi quality
$effect->setVideoOutputSettings(
    array(
        'format' => 'mpegts',
        'preset' => 'veryslow',
        'crf' => 16,
    )
);
#echo "Settings for output video ffmpeg:";
#echo var_dump($effect->getVideoOutputSettings());

# show ffmpeg output settings
#
##########################

$input = $params['info']['input'];
$outputPrefix = $params['info']['outputPrefix'];
$outputHeight = $params['info']['outputHeight'];

$returnArray = array();
$returnArray['audio'] = array();
$returnArray['video'] = array();
$returnArray['timeline'] = array();
$returnArray['info'] = $params['info'];


# save audio stream
###########################################################
$outputAudio = "${outputPrefix}_audio.aac";
$cmd = $effect->getAudio($input, $outputAudio);
if (!$cmd) {
    echo $effect->getLastError();
    exit(1);
}
if (!$effect->doExec($cmd)) {
    $effect->writeToLog("Someting wrong with command: $cmd");
    exit(1);
}

$stream = null;
$effect->getStreamInfo($outputAudio, "audio", $stream);
array_push($returnArray['audio'], array("filename" => $outputAudio, "stream" => $stream));
###########################################################

foreach ($params['timeline'] as $timeline) {
    $start = $timeline['start'];
    $end = $timeline['end'];
    $output = "${outputPrefix}_${start}_${end}.ts";
    $animationFilename = "${outputPrefix}_${start}_${end}_animation.ts";

    $cmd = $effect->accurateSplitScaleVideo(
        $input,
        $output,
        $start,
        $end,
        $outputHeight,
        true
    );

    if (!$cmd) {
        echo $effect->getLastError();
        exit(1);
    }
    if (!$effect->doExec($cmd)) {
        $effect->writeToLog("Someting wrong with command: $cmd");
        exit(1);
    }
    $stream = null;
    $effect->getStreamInfo($output, "video", $stream);

    $timeline['filename']=$output;
    #$timeline['animationFilename']=$output;
    array_push($returnArray['timeline'], $timeline);
    array_push($returnArray['video'], array("filename" => $output,  "stream" => $stream));    
}

file_put_contents($outputJson, json_encode($returnArray, JSON_PRETTY_PRINT));
exit(0);

function help($msg)
{
    fwrite(STDERR,
        "$msg
    Parse the json file and split input video by timeline to parts
    Output in json format for each part:
        filename
        video stream properties

	Usage:$0 -j file.json -o ouput.json
	\n");
    exit(-1);
}
