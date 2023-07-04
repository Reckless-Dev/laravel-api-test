pipeline {
	agent any
	stages {

		stage("Sonarqube") {
    	environment {
        scannerHome = tool 'SonarQubeScanner'
    	}    
			steps {
        withSonarQubeEnv('sonarqube') {
          bat "${scannerHome}/bin/sonar-scanner \
					-D sonar.login=admin \
					-D sonar.password=Barantum~!888 \
					-D sonar.projectKey=backend-services \
					-D sonar.exclusion=app/http/controllers \
					-D sonar.host.url=http://192.168.100.212:9000"
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