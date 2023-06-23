pipeline {
    agent any

    stages {
        stage('Pre-Build') {
            steps {
                echo 'Pre-Build...'
                echo 'Send status Pre-Build to Mail, Telegram, Slack...'
            }
        }
        stage('Build') {
            steps {
                echo 'Building...'
                echo 'Running docker build...'

                sh '''
                    composer install --prefer-dist
                '''
            }
        }
        stage('Test') {
            steps {
                echo 'Testing..'
            }
        }
        stage('Push') {
            steps {
                echo 'Pushing...'
                echo 'Running docker push...'
            }
        }
        stage('PHPUnit Tests') {
            steps {
                catchError(buildResult: 'FAILURE', stageResult: 'FAILURE') {
                    sh '''
                        ./vendor/bin/phpunit tests/Unit
                           
                    '''
                }

                junit 'reports/unitreport.xml'

                publishHTML([
                    allowMissing: true,
                    alwaysLinkToLastBuild: false,
                    keepAll: true,
                    reportDir: 'reports/coverage',
                    reportFiles: 'index.html',
                    reportName: 'PHPUnit Test Coverage Report'
                ])
            }
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
