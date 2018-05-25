<?php

# This script split video to parts
# korolev-ia@yandex.ru
#

require_once "./FfmpegEffects.php";
##########################
# Set enviroment FONTCONFIG_FILE . This require for addTextAss effect
$basedir = dirname(__FILE__);

$options = getopt('j:');

$json_file = isset($options['j']) ? $options['j'] : false;

if (!$json_file) {
    help("Need parameter json file ( -j file.json ) ");
}

if (!file_exists($json_file)) {
    help("File $json_file do not exist");
}

$string = file_get_contents($json_file);
$params = json_decode($string, true);
if (!$params) {
    help("Cannot decode json from $json_file");
}

# new instance for FfmpegEffects
$effect = new FfmpegEffects();

$effect->setGeneralSettings(
    array(
        'ffmpegLogLevel' => 'info',
        'showCommand' => false
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
        'crf' => 1,
    )
);
#echo "Settings for output video ffmpeg:";
#echo var_dump($effect->getVideoOutputSettings());

# show ffmpeg output settings
#
##########################


$input = $params['input'];
$outputPrefix = $params['outputPrefix'];
$outputHeight = $params['outputHeight'];



$returnArray = array();
$returnArray['audio']=array();
$returnArray['video']=array();


# save audio stream
###########################################################
$outputAudio = "${outputPrefix}_audio.aac";
$cmd = $effect->getAudio( $input, $outputAudio);
if (!$cmd) {
    echo $effect->getLastError();
    exit(1);
}
if (!$effect->doExec($cmd)) {
    $effect->writeToLog("Someting wrong with command: $cmd");
    exit(1);
}

$stream = null;
$effect->getStreamInfo( $outputAudio, "audio", $stream );
array_push($returnArray['audio'], array("filename" => $outputAudio, "stream" => $stream));
###########################################################

foreach ($params['timeline'] as $timeline) {
    $start = $timeline['start'];
    $end = $timeline['end'];
    $output = "${outputPrefix}_${start}_${end}.ts";

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
    $effect->getStreamInfo( $output, "video", $stream );
    array_push($returnArray['video'], array("filename" => $output, "stream" => $stream));

}

print json_encode($returnArray, JSON_PRETTY_PRINT);
exit(0);

function help($msg)
{
    fwrite(STDERR,
        "$msg
    Parse the json file and split input video by timeline to parts
    Output in json format for each part:
        filename
        video stream properties

	Usage:$0 -j file.json
	\n");
    exit(-1);
}
