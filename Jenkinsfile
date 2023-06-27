  node {
    stage("composer_install") {
    	sh 'composer install'
  	}

    stage("generate_key") {
    	sh 'php artisan key:generate'
  	}

		stage("phpunit") {
			sh './vendor/bin/phpunit tests/Unit'
		}
  }

