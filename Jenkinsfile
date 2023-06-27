pipeline {
  node {
    stage("composer_install") {
    	sh 'composer install'
  	}

    stage("generate_key") {
    	sh 'php artisan key:generate'
  	}

		stage("phpunit") {
			sh './vendor/bin/phpunit tests/Unit'
		}

  }
  post {
    success {
        echo 'Success...'
        echo 'Send status Success to Mail, Telegram, Slack...'
    }
    failure {
      echo 'Failure...'
      echo 'Send status Failure to Mail, Telegram, Slack...'
    }
  }

}
