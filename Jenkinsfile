  node {
		stage('preparation') {
			git branch: 'master', url: 'https://github.com/Reckless-Dev/laravel-api-test.git'
		}
		
    stage("composer_install") {
    	sh 'composer install'
  	}

		stage("phpunit") {
			sh './vendor/bin/phpunit tests/Unit'
		}
  }

