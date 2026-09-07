.PHONY: all cc build

all: cc build

cc:
	php bin/console cache:clear

build:
	npm run build
