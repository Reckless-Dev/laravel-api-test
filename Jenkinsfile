pipeline {
	agent any
	stages {
		stage("Preparation") {
			steps {
				echo 'git clone...'
				// git branch: 'master', url: 'https://github.com/Reckless-Dev/laravel-api-test.git'
			}
		}

 		stage("Composer Install") {
			steps {
				echo 'composer install...'
 				bat 'composer install'
			}
		}

		stage("PHPUnit") {
			steps {
				echo 'running the phpunit test...'
				bat './vendor/bin/phpunit tests/Unit'
			}
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