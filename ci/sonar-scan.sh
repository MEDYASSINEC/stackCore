#!/bin/bash
# ci/sonar-scan.sh
# Script pour lancer l'analyse SonarQube

# 💡 Arrêter le script si une commande échoue
set -e

# Variables SonarQube – à adapter
SONAR_HOST_URL="http://localhost:9000"  # ou ton serveur SonarQube
SONAR_PROJECT_KEY="stackcore"
SONAR_LOGIN="sqp_5fc305631f0e73d435e342d7a998415c7493719b"

# Vérifier que Java est installé
if ! command -v java &> /dev/null
then
    echo "Java n'est pas installé. Installation requise."
    exit 1
fi

# Vérifier si sonar-scanner est installé
if [ ! -f "./ci/sonar-scanner-5.0.1.3006-linux/bin/sonar-scanner" ]; then
    echo "Installation de SonarScanner..."
    mkdir -p ci
    curl -sSLo ci/sonar-scanner.zip https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-5.0.1.3006-linux.zip
    unzip -o ci/sonar-scanner.zip -d ci/
fi

# Ajouter sonar-scanner au PATH
export PATH="$PATH:$(pwd)/ci/sonar-scanner-5.0.1.3006-linux/bin"

# Afficher la version de sonar-scanner
sonar-scanner -v

# Lancer l'analyse
sonar-scanner \
  -Dsonar.projectKey="$SONAR_PROJECT_KEY" \
  -Dsonar.sources=. \
  -Dsonar.host.url="$SONAR_HOST_URL" \
  -Dsonar.login="$SONAR_LOGIN"

echo "✅ Analyse SonarQube terminée."
