pipeline {
	agent any
	stages {

		stage('SCM') {
  	  checkout scm
  	}

  	stage('SonarQube Analysis') {
  	  def scannerHome = tool 'SonarScanner';
  	  withSonarQubeEnv() {
  	    bat "${scannerHome}/bin/sonar-scanner"
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