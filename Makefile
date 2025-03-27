tests:
	php bin/console d:d:d --force --if-exists --env=test
	php bin/console d:d:c --env=test	
	php bin/console d:m:m --no-interaction --env=test
	php bin/console d:f:l --no-interaction --env=test
	APP_ENV=test php bin/phpunit tests/Form/ContactTypeTest.php
	APP_ENV=test php bin/phpunit tests/LoginTest.php
APP_ENV=test php bin/phpunit tests/LoginTest.php --testdox



 