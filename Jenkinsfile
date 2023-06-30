pipeline {
	agent { docker { image 'php:8.1.0-alpine' } }
	stages {
		stage("Preparation") {
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