# ci/sonar-scan.ps1
Write-Host "🚀 Démarrage de l'analyse SonarQube..."

# Variables SonarQube
$SONAR_HOST_URL = "http://localhost:9000"
$SONAR_PROJECT_KEY = "stackcore"
$SONAR_LOGIN = "sqp_5fc305631f0e73d435e342d7a998415c7493719b"

# Vérifier Java
if (-not (Get-Command java -ErrorAction SilentlyContinue)) {
    Write-Host "Java n'est pas installé. Installation requise."
    exit 1
}

# Vérifier SonarScanner
$scannerPath = ".\ci\sonar-scanner-5.0.1.3006-windows\bin\sonar-scanner.bat"
if (-not (Test-Path $scannerPath)) {
    Write-Host "Téléchargement de SonarScanner..."
    mkdir ci
    Invoke-WebRequest -Uri "https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-windows.zip" -OutFile "ci\sonar-scanner.zip"
    Expand-Archive -Path "ci\sonar-scanner.zip" -DestinationPath "ci" -Force
}

# Lancer l'analyse
& $scannerPath `
    -Dsonar.projectKey=$SONAR_PROJECT_KEY `
    -Dsonar.sources=. `
    -Dsonar.host.url=$SONAR_HOST_URL `
    -Dsonar.login=$SONAR_LOGIN

Write-Host "✅ Analyse SonarQube terminée."
