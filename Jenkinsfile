pipeline {
	stages {
		stage('preparation') {
			git branch: 'master', url: 'https://github.com/Reckless-Dev/laravel-api-test.git'
		}

 		stage("composer_install") {
 			bat 'composer install'
		}

		stage("phpunit") {
			bat './vendor/bin/phpunit tests/Unit'
		}
  }
	post {
    success {
        echo 'Success!'
    }
    failure {
         echo 'Failed!'
    }
  }
}