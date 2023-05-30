#!/bin/bash

  path=$1
  flipStatus=$2
  output='output.mp4'


  # variables
  speed=$(echo "scale=2; (80 + $RANDOM % 34) / 100" | bc)
  contrast=$(echo "scale=2; (85 + $RANDOM % 16) / 100" | bc)
  noise=$(echo "scale=2; (5 + $RANDOM % 66) / 100" | bc)
  saturation=$(echo "scale=2; (95 + $RANDOM % 11) / 100" | bc)
  brightness=$(echo "scale=2; (-8 + $RANDOM % 17) / 100" | bc)



  if [[ "$flipStatus" == "true" ]]; then
      filters+="hflip,"
  fi

  filters+="setpts=$speed*PTS,"
  filters+="eq=contrast=$contrast:brightness=$brightness,"
  filters+="noise=alls=$noise:allf=t,"
  filters+="eq=saturation=$saturation"

  # apply filters and save
 ffmpeg -y -i "$path" -filter_complex "transpose=2,transpose=2,format=yuv420p,geq=lum_expr='p(X,Y+10)',hflip,vflip,$filters" -metadata:s:v rotate=0 -metadata title="" -metadata artist="" -metadata album_artist="" -metadata album="" -metadata date="" -metadata track="" -metadata genre="" -metadata publisher="" -metadata encoded_by="" -metadata copyright="" -metadata composer="" -metadata performer="" -metadata TIT1="" -metadata TIT3="" -metadata disc="" -metadata TKEY="" -metadata TBPM="" -metadata language="eng" -metadata encoder="" -codec:v libx264 -pix_fmt yuv420p -r 30 -g 60 -b:v 2000k -profile:v main -level 3.1 -c:a libmp3lame -b:a 128k -ar 44100 -preset superfast "$output"
 echo "$output"
