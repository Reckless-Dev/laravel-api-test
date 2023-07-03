pipeline {
	agent any
	stages {

		stage("Sonarqube") {
    	environment {
        scannerHome = tool 'SonarQubeScanner'
    	}    
			steps {
        withSonarQubeEnv('sonarqube') {
          bat "\"${scannerHome}\\bin\\sonar-scanner.bat\""
        } 
				// timeout(time: 10, unit: 'MINUTES') {
        //   waitForQualityGate abortPipeline: true
        // }
    	}
		}

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