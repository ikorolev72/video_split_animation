<?php

require_once "FfmpegSubtitlesLib.php";
require_once "./FfmpegEffects.php";

$effect = new FfmpegEffects();


##########################
# Set enviroment FONTCONFIG_FILE . This require for addTextAss effect
$basedir = dirname(__FILE__);
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    putenv("FONTCONFIG_FILE=$basedir\fonts.conf"); # for windows
}

#$tempDir=sys_get_temp_dir();
#$tempDir="$basedir/";
$tempDir="./";

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


# we must split with slow speed and hi quality
$effect->setVideoOutputSettings(
  array(
      'format' => 'mpegts',
      'preset' => 'veryfast',
      'crf' => 23,
  )
);

$input = $params['input'];
$outputPrefix = $params['outputPrefix'];
$outputHeight = $params['outputHeight'];

$i=0;
foreach ($params['timeline'] as $timeline) {
#echo var_dump( $sub_edited->subtitles );
    #$assFile = "${outputPrefix}_${start}_${end}.ass";
    if (isset($timeline['animation'])) {
        $start = $timeline['start'];
        $end = $timeline['end'];
        $assFile = tempnam($tempDir, 'Tux') . ".ass";

        $subtitleEffect = new FfmpegSubtitlesLib();
        $subtitleEdited = new FfmpegSubtitlesLib();

        $subtitleEdited->subtitles[0] = array();
        $subtitleEdited->subtitles[0]['style'] = 'Default';
        $subtitleEdited->subtitles[0]['text'] = $timeline['animation']['text'];
        $subtitleEdited->subtitles[0]['first_line'] = $timeline['animation']['text'];
        $subtitleEdited->subtitles[0]['start'] = $timeline['animation']['start'];
        $subtitleEdited->subtitles[0]['end'] = $timeline['animation']['end'];
        $subtitleEdited->subtitles[0]['end_part'] = '';
        $i++;

        $subtitleEffect->subtitles = array_merge($subtitleEffect->subtitles, $subtitleEdited->kineticTypography(0, 1, $timeline['animation']['effect']));

        #$subtitleEffect->printSubtitles();
        $subtitleEffect->writeSubtitles($assFile);

        # todo
        $videoFile = "${outputPrefix}_${start}_${end}.ts";
        $tmpVideoFile = "${outputPrefix}_${start}_${end}_animation.ts";
        #$tmpVideoFile = tempnam(sys_get_temp_dir(), 'Tux') . ".ts";

        $cmd = $effect->addAnimation(
            $videoFile,
            $assFile,
            $tmpVideoFile
        );

        if (!$cmd) {
            echo $effect->getLastError();
            exit(1);
        }
        /*
        if (!$effect->doExec($cmd)) {
        $effect->writeToLog("Someting wrong while add animation : $cmd");
        exit(1);
        }
         */
        #print "$videoFile,$tmpVideoFile" . PHP_EOL;
        #unlink($assFile);
    }
}
#$subtitleEdited->printSubtitles();
#echo var_dump( $subtitleEdited->subtitles );

/*
$cmd = $effect->addAnimation (
$input,
$inputAudio,
$output
) ;

if (!$cmd) {
echo $effect->getLastError();
exit(1);
}
if (!$effect->doExec($cmd)) {
$effect->writeToLog("Someting wrong while split file by command: $cmd");
exit(1);
}
 */
exit(0);

function help($msg)
{
    fwrite(STDERR,
        "$msg
    Generate ass files for animation


	Usage:$0 -j file.json
	\n");
    exit(-1);
}
