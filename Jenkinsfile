pipeline {
    agent any
    environment {
        APP_URL = 'http://192.168.144.142'
        SONAR_PROJECT_KEY = 'allevent-backend'
    }
    triggers { githubPush() }
    stages {
        stage('Clone') {
            steps {
                git credentialsId: 'github-token',
                    url: 'https://github.com/Small-Danger/all-event-backend.git',
                    branch: 'main'
            }
        }
        stage('Installation dependances') {
            steps {
                sh 'composer install --no-interaction --prefer-dist'
            }
        }
        stage('Tests unitaires') {
            steps {
                sh 'php artisan test || true'
            }
        }
        stage('SAST - SonarQube') {
    steps {
        withSonarQubeEnv('sonarqube') {
            sh '''
                /opt/sonar-scanner/bin/sonar-scanner \
                -Dsonar.projectKey=allevent-backend \
                -Dsonar.sources=. \
                -Dsonar.exclusions=vendor/**,node_modules/** \
                -Dsonar.host.url=http://192.168.144.142:9000
            '''
        }
    }
}
        stage('SAST - Audit Composer') {
            steps {
                sh 'composer audit || true'
            }
        }
        stage('Secrets - Gitleaks') {
            steps {
                sh 'gitleaks detect -s . -v --log-opts="HEAD~1..HEAD" || true'
            }
        }
        stage('Build Docker') {
            steps {
                sh 'docker build -t allevent-backend:latest .'
            }
        }
	stage('Push Docker Hub') {
    steps {
        withCredentials([usernamePassword(
            credentialsId: 'dockerhub-credentials',
            usernameVariable: 'DOCKER_USER',
            passwordVariable: 'DOCKER_PASS'
        )]) {
            sh '''
                echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin
                docker tag allevent-backend:latest $DOCKER_USER/allevent-backend:latest
                docker push $DOCKER_USER/allevent-backend:latest
            '''
        }
    }
}
        stage('Scan Trivy') {
            steps {
                sh 'trivy image --exit-code 0 --severity HIGH,CRITICAL allevent-backend:latest || true'
            }
        }
        stage('DAST - ZAP') {
            steps {
                sh 'zaproxy -cmd -quickurl ${APP_URL} -quickprogress || true'
            }
        }
    }
    post {
        success { echo 'Pipeline DevSecOps Backend reussi !' }
        failure { echo 'Pipeline echoue - verifier les logs' }
    }
}
