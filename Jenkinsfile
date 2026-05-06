pipeline {
    agent any
    environment {
        SONAR_PROJECT_KEY = 'allevent-backend'
        APP_URL = 'http://192.168.226.128'
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
                        -Dsonar.projectKey=${SONAR_PROJECT_KEY} \
                        -Dsonar.sources=. \
                        -Dsonar.exclusions=vendor/**,node_modules/**,*.js \
                        -Dsonar.host.url=http://192.168.226.128:9000
                    '''
                }
            }
        }
        stage('SAST - Audit Composer') {
            steps {
                sh 'composer audit || true'
            }
        }
        stage('SCA - OWASP Dependency Check') {
            steps {
                sh '''
                    /opt/dependency-check/bin/dependency-check.sh \
                    --project allevent-backend \
                    --scan . \
                    --exclude "**/vendor/**" \
                    --format HTML \
                    --out ./dependency-check-report \
                    --nvdApiKey nokey \
                    || true
                '''
            }
        }
        stage('Secrets - Gitleaks') {
            steps {
                sh 'gitleaks detect -s . -v --log-opts="HEAD~1..HEAD" || true'
            }
        }
        stage('Secrets - Vault') {
            steps {
                withVault(
                    vaultSecrets: [[
                        path: 'secret/allevent',
                        secretValues: [
                            [envVar: 'VAULT_GITHUB_TOKEN', vaultKey: 'github_token'],
                            [envVar: 'VAULT_DOCKER_USER', vaultKey: 'dockerhub_user'],
                            [envVar: 'VAULT_DOCKER_TOKEN', vaultKey: 'dockerhub_token'],
                            [envVar: 'VAULT_SONAR_TOKEN', vaultKey: 'sonarqube_token']
                        ]
                    ]]
                ) {
                    sh 'echo "Secrets Vault charges avec succes !"'
                    sh 'echo "Docker User: $VAULT_DOCKER_USER"'
                }
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
        stage('Deploy Docker Compose') {
            steps {
                sh '''
                    cd /home/killi/allevent-deploy
                    docker compose pull
                    docker compose up -d
                '''
            }
        }
    }
    post {
        success { echo 'Pipeline DevSecOps Backend reussi !' }
        failure { echo 'Pipeline echoue - verifier les logs' }
    }
}
