@echo off
setlocal enabledelayedexpansion

echo 🚀 Démarrage de l'analyse SonarQube...

if not defined SONAR_HOST_URL set SONAR_HOST_URL=http://localhost:9000
set SONAR_PROJECT_KEY=stackcore
if not defined SONAR_TOKEN set SONAR_TOKEN=%SONAR_TOKEN%

java -version >nul 2>&1
if errorlevel 1 (
    echo ❌ Java n'est pas installé.
    exit /b 1
)

if not exist "ci\sonar-scanner-5.0.1.3006-windows\bin\sonar-scanner.bat" (
    echo 📦 Installation de SonarScanner...
    if not exist "ci" mkdir ci
    powershell -Command "Invoke-WebRequest -Uri 'https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-windows.zip' -OutFile 'ci\sonar-scanner.zip'"
    powershell -Command "Expand-Archive -Path 'ci\sonar-scanner.zip' -DestinationPath 'ci\'"
    del ci\sonar-scanner.zip
)

set PATH=%PATH%;%cd%\ci\sonar-scanner-5.0.1.3006-windows\bin

sonar-scanner -v

echo 🔍 Lancement du scan...
sonar-scanner ^
  -Dsonar.projectKey=%SONAR_PROJECT_KEY% ^
  -Dsonar.sources=. ^
  -Dsonar.host.url=%SONAR_HOST_URL% ^
  -Dsonar.login=%SONAR_TOKEN%

echo ✅ Analyse SonarQube terminée.