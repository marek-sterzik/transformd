install: all

all: highlight.make
	mkdir -p ../source/style
	cp highlight/style.css ../source/style/highlight.css
	cp highlight/highlight.js ../source/style/markdown.js
	cp css/style.css ../source/style/style.css
	cp css/paper.jpg ../source/style/background.jpg

highlight.make:
	cd highlight && make
