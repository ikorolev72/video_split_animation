@echo off
php animation2.php -j splited.json -o animation.json
php stitch.php -j animation.json -o output.mp4