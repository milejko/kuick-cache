PHP_VERSION ?= 8.5
IMAGE_NAME := kuickphp/cache

.PHONY: test
test:
	# generate CI_TAG to avoid concurrent run collisions
	$(eval CI_TAG := $(IMAGE_NAME):$(shell date +%s%N))
	docker build --build-arg PHP_VERSION=$(PHP_VERSION) --tag $(CI_TAG) .
	docker run --rm -v ./:/var/www/html $(CI_TAG) sh -c "composer up && composer fix:phpcbf && composer test:all"
	docker image rm $(CI_TAG)
