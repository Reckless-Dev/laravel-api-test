  node {
    stage("composer_install") {
    	sh 'composer install'
  	}

		stage("phpunit") {
			sh './vendor/bin/phpunit tests/Unit'
		}
  }

