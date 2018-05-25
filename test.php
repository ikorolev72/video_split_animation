<?php

require_once "./FfmpegEffects.php";
##########################
# Set enviroment FONTCONFIG_FILE . This require for addTextAss effect
$basedir = dirname(__FILE__);
putenv("FONTCONFIG_FILE=$basedir\\etc\\fonts.conf"); # for windows

# new instance for FfmpegEffects
$effect = new FfmpegEffects();
echo "General settings:";

echo var_dump($effect->getGeneralSettings());
$effect->setGeneralSettings(
    array('ffmpegLogLevel' => 'warning')
);


# set ffmpeg new audio output settings
$effect->setAudioOutputSettings(
    array(
        'bitrate' => '128k',
        'codec' => 'aac',
    )
);


$effect->setVideoOutputSettings(
  array(
      'format' => 'mpegts',
      'preset' => 'veryfast',
      'crf' => 18,
  )
);
echo "Settings for output video ffmpeg:";
echo var_dump($effect->getVideoOutputSettings());

# show ffmpeg output settings
#echo "New settings for output audio ffmpeg:";
#echo var_dump($effect->getAudioOutputSettings());
#
##########################
$splitVideoParamsFile="split_video.json";
$splitParams=json_decode ( file_get_contents( $splitVideoParamsFile ), true);
#echo var_dump( $splitParams['timeline'] );

$commands=$effect->accurateSplitVideo( $splitParams ) ;
if( !$commands ) {
  echo $effect->getLastError();
}
echo var_dump( $commands );

