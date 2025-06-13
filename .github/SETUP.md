# GitHub Actions Setup

Ce document explique comment configurer les secrets GitHub nécessaires pour les workflows CI/CD.

## 🔐 Secrets requis

### Pour les releases automatiques

1. **PACKAGIST_USERNAME** : Votre nom d'utilisateur Packagist
2. **PACKAGIST_TOKEN** : Votre token API Packagist
   - Générez-le sur : https://packagist.org/profile/
   - Allez dans "API Token" et créez un nouveau token

### Pour les notifications (optionnel)

3. **DISCORD_WEBHOOK** : URL du webhook Discord pour les notifications
   - Créez un webhook dans votre serveur Discord
   - Copiez l'URL complète

## 📝 Comment ajouter les secrets

1. Allez sur votre repository GitHub
2. Settings → Secrets and variables → Actions
3. Cliquez sur "New repository secret"
4. Ajoutez chaque secret avec sa valeur

## 🚀 Workflows disponibles

### 1. Tests (`tests.yml`)
- Se déclenche sur push/PR vers main/develop
- Teste sur PHP 8.2, 8.3, 8.4
- Teste sur Laravel 11, 12

### 2. Qualité du code (`code-quality.yml`)
- Se déclenche sur push/PR vers main/develop
- Vérifie le formatage avec PHP CS Fixer
- Analyse statique avec PHPStan

### 3. Release (`release.yml`)
- Se déclenche sur push de tag `v*.*.*`
- Génère automatiquement le changelog
- Crée une release GitHub
- Met à jour Packagist

### 4. Métriques (`metrics.yml`)
- Se déclenche quotidiennement
- Collecte les stats Packagist
- Génère un rapport de performance
- Notifications optionnelles

## 🏷️ Comment créer une release

```bash
# 1. Mettez à jour la version dans composer.json
# 2. Commitez les changements
git add .
git commit -m "Release v1.2.0"

# 3. Créez et poussez le tag
git tag v1.2.0
git push origin v1.2.0

# Le workflow release.yml se déclenche automatiquement
```

## 🛠 Scripts composer disponibles

```bash
# Tests
composer test                 # Tous les tests
composer test-coverage        # Tests avec couverture
composer test-unit           # Tests unitaires uniquement
composer test-feature        # Tests fonctionnels uniquement

# Qualité du code
composer analyse             # Analyse PHPStan
composer format              # Formatage automatique
composer format-check        # Vérification du formatage
composer quality             # Toutes les vérifications
```

## 🔧 Configuration locale

Pour installer les outils de qualité localement :

```bash
# Installer PHPStan et PHP CS Fixer
composer require --dev phpstan/phpstan friendsofphp/php-cs-fixer

# Lancer les vérifications
composer quality
```

