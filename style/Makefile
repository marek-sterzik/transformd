all: js css

download:
	./download-resources.sh

js:
	(cat highlight.min.js; echo; cat init.js) > ../source/style/markdown.js

css:
	cp default.min.css ../source/style/highlight.css

