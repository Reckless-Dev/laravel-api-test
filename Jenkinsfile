// ================================================================================================
// SonarQube Scanner Settings
// ------------------------------------------------------------------------------------------------

// The project key of your SonarQube project
def SONAR_PROJECT_KEY = 'backend-services'

// The token of your SonarQube project
def SONAR_TOKEN = 'sqp_c6fddbc2f0baea58adce669567c037c8af4f9799'
// ================================================================================================

pipeline {
	agent any
	stages {
		stage("Preparation") {
			steps {
				echo 'git clone...'
				// git branch: 'master', url: 'https://github.com/Reckless-Dev/laravel-api-test.git'
			}
		}

		stage('clonning from GIT'){
			steps {
				checkout scmGit(branches: [[name: '*/master']], extensions: [], userRemoteConfigs: [[credentialsId: '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', url: 'https://github.com/Reckless-Dev/laravel-api-test.git']])
			}
    }

    stage('SonarQube Analysis') {
			steps {
				script {
	    		def scannerHome = tool 'sonarqube'
				}
	      withSonarQubeEnv('sonarqube-server') {
	      	bat """C:/ProgramData/Jenkins/.jenkins/tools/hudson.plugins.sonar.SonarRunnerInstallation/SonarQubeScanner/bin/sonar-scanner \
	     		-D sonar.projectVersion=1.0-SNAPSHOT \
	       	-D sonar.login=admin \
	      	-D sonar.password=Barantum~!888 \
	      	-D sonar.token=${SONAR_TOKEN} \
	        -D sonar.projectKey=${SONAR_PROJECT_KEY} \
	        -D sonar.sourceEncoding=UTF-8 \
	        -D sonar.language=php \
	        -D sonar.exclusions=vendor/** \
					-D sonar.verbose=true \
	        -D sonar.host.url=http://192.168.100.212:9000/"""
	      }
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