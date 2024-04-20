#!/bin/bash

while read url; do
	filename="`basename "$url"`"
	wget -O "$filename" "$url"
done < resources.url
