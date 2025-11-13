@echo off
REM ci/sonar-scan.bat
REM Script pour lancer l'analyse SonarQube sur Windows

echo 🚀 Démarrage de l'analyse SonarQube...
setlocal enabledelayedexpansion

REM Lire depuis les variables d'environnement ou utiliser les valeurs par défaut
if not defined SONAR_HOST_URL set SONAR_HOST_URL=http://localhost:9000
set SONAR_PROJECT_KEY=stackcore
if not defined SONAR_TOKEN set SONAR_TOKEN=sqp_5fc305631f0e73d435e342d7a998415c7493719b

REM Vérifier que Java est installé
java -version >nul 2>&1
if errorlevel 1 (
    echo ❌ Java n'est pas installé.
    exit /b 1
)

REM Vérifier si sonar-scanner est installé
if not exist "ci\sonar-scanner-5.0.1.3006-windows\bin\sonar-scanner.bat" (
    echo 📦 Installation de SonarScanner...
    if not exist "ci" mkdir ci
    powershell -Command "Invoke-WebRequest -Uri 'https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-windows.zip' -OutFile 'ci\sonar-scanner.zip'"
    powershell -Command "Expand-Archive -Path 'ci\sonar-scanner.zip' -DestinationPath 'ci\'"
    del ci\sonar-scanner.zip
)

REM Ajouter sonar-scanner au PATH
set PATH=%PATH%;%cd%\ci\sonar-scanner-5.0.1.3006-windows\bin

REM Afficher la version de sonar-scanner
sonar-scanner -v

REM Lancer l'analyse
echo 🔍 Lancement du scan...
sonar-scanner ^
  -Dsonar.projectKey=%SONAR_PROJECT_KEY% ^
  -Dsonar.sources=. ^
  -Dsonar.host.url=%SONAR_HOST_URL% ^
  -Dsonar.login=%SONAR_TOKEN%

echo ✅ Analyse SonarQube terminée.