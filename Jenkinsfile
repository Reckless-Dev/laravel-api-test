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
			checkout scmGit(branches: [[name: '*/master']], extensions: [], userRemoteConfigs: [[credentialsId: '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', url: 'https://github.com/Reckless-Dev/laravel-api-test.git']])
    }

    stage('SonarQube Analysis') {
    	def scannerHome = tool 'sonarqube'
      withSonarQubeEnv('sonarqube-server') {
      	bat """C:/ProgramData/Jenkins/.jenkins/tools/hudson.plugins.sonar.SonarRunnerInstallation/SonarQubeScanner/bin/sonar-scanner \
     		-D sonar.projectVersion=1.0-SNAPSHOT \
       	-D sonar.login=admin \
      	-D sonar.password=Barantum~!888 \
      	-D sonar.token=sqp_c6fddbc2f0baea58adce669567c037c8af4f9799 \
      	-D sonar.projectBaseDir=C:/ProgramData/Jenkins/.jenkins/workspace/sonarqube-good-code/ \
        -D sonar.projectKey=backend-services \
        -D sonar.sourceEncoding=UTF-8 \
        -D sonar.language=php \
        -D sonar.sources=app/Http/Controllers \
        -D sonar.exclusions=app/Http/Middleware/*.php \
        -D sonar.host.url=http://192.168.100.212:9000/"""
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