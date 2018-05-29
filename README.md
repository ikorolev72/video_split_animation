## Split

### How to run
Edit file `split_video.json`, change path to input video, timeline.

Run script 
```
php split.php -j /path/split_video.json -o /path/splited.json
```

Script print to stdout in json format array for each part :
```
{
  "info": {
    "input": "ramzan_empty.mp4",
    "outputHeight": 480
  },
  "timeline": [
    {
      "start": 0,
      "end": 3.12,
    },    
    ...
    {
      "start": 3.12,
      "end": 6.12,
    }
  ]   
    "audio": [
        {
            "filename": "output_audio.aac",
        }
    ],
    ...
    "video": [
        {
            "filename": "output_0_3.12.ts",
        },
        {
            "filename": "output_3.12_4.12.ts",
        }
    ]
}
```

## Animation
This script do the animation by input json.
Now 

### How to run
Take json file in format like output of `split.php` 

Run script 
```
php animation.php -j /path/splited.json -o /path/animation.json
```



## Stitch
This script concatenate video parts into entire video and merge with audio stream.

### How to run
Take json file in format like output of `animation.php` 

Run script 
```
php stitch.php -j /path/animation.json -o output.mp4
```