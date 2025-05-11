extract = $(shell grep -A2 $(1) Plugin.php | tail -n1 | tr -d " ;'" | sed "s/return//")

plugin = $(call extract, getPluginName)
version = $(call extract, getPluginVersion)

all:
	@echo "Build archive for plugin ${plugin} version=${version}"
	@git archive HEAD . :!.github :!.gitignore :!Test :!ChangeLog.md :!README.md :!Makefile --prefix=${plugin}/ --format=zip -9 -o ${plugin}-${version}.zip
