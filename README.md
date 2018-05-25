## Split

### How to run
Edit file `split_video.json`, change path to input video, timeline.

Run script 
```
php split.php -j /path/split_video.json
```

Script print to stdout in json format array for each part :
```
[
    {
        "filename": "output_0_3.12.ts",
        "stream": {
            "index": 0,
            "codec_name": "h264",
            "codec_long_name": "H.264 \/ AVC \/ MPEG-4 AVC \/ MPEG-4 part 10",
            "profile": "High",
            "codec_type": "video",
            "codec_time_base": "1001\/60000",
            "codec_tag_string": "[27][0][0][0]",
    },
...
    {
        "filename": "output_3.12_4.12.ts",
        "stream": {
            "index": 0,
            "codec_name": "h264",
            "codec_long_name": "H.264 \/ AVC \/ MPEG-4 AVC \/ MPEG-4 part 10",
    }
]

```

## Split

### How to run
Edit file `split_video.json`, change path to input video, timeline.

Run script 
```
php split.php -j /path/split_video.json
```

Script print to stdout in json format array for each part :
```
{
    "audio": [
        {
            "filename": "output_audio.aac",
            "stream": {
                "index": 0,
                "codec_name": "aac",
                "codec_long_name": "AAC (Advanced Audio Coding)",
                "profile": "LC",
            }
        }
    ],
    ...
    "video": [
        {
            "filename": "output_0_3.12.ts",
            "stream": {
                "index": 0,
                "codec_name": "h264",
                "codec_long_name": "H.264 \/ AVC \/ MPEG-4 AVC \/ MPEG-4 part 10",
                "profile": "High",
                 }
            }
        },
        {
            "filename": "output_3.12_4.12.ts",
            "stream": {
                "index": 0,
                "codec_name": "h264",
                "codec_long_name": "H.264 \/ AVC \/ MPEG-4 AVC \/ MPEG-4 part 10",
                "profile": "High",
           }
        }
    ]
}
```

## Stitch
This script concatenate video parts into entire video and merge with audio stream.

### How to run
Take json file in format like output of `split.php` 

Run script 
```
php stitch.php -j /path/video_parts.json -o output.mp4
```