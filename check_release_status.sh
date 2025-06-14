#!/bin/bash

# Script pour vérifier le statut de la release v2.1.0

echo "🔍 Checking Laravel Arc v2.1.0 Release Status"
echo "============================================"

# 1. Vérifier les tags Git locaux
echo "📋 Local Git Tags:"
git tag -l --sort=-version:refname | head -5
echo ""

# 2. Vérifier les tags distants
echo "🌐 Remote Git Tags:"
git ls-remote --tags origin | grep "v2.1.0" && echo "✅ v2.1.0 tag exists on remote" || echo "❌ v2.1.0 tag not found on remote"
echo ""

# 3. Vérifier Packagist
echo "📦 Packagist Status:"
packagist_response=$(curl -s "https://packagist.org/packages/grazulex/laravel-arc.json")
if echo "$packagist_response" | grep -q "v2.1.0"; then
    echo "✅ v2.1.0 is available on Packagist"
    echo "   Latest version: $(echo "$packagist_response" | jq -r '.package.versions | keys | .[0]' 2>/dev/null || echo 'Unable to parse')"
else
    echo "⏳ v2.1.0 not yet available on Packagist (webhooks may take a few minutes)"
fi
echo ""

# 4. Vérifier GitHub Releases
echo "🚀 GitHub Release Status:"
github_url="https://api.github.com/repos/Grazulex/laravel-arc/releases/tags/v2.1.0"
if curl -s "$github_url" | grep -q '"tag_name"'; then
    echo "✅ GitHub release v2.1.0 exists"
    echo "   URL: https://github.com/Grazulex/laravel-arc/releases/tag/v2.1.0"
else
    echo "❌ GitHub release v2.1.0 not yet created"
    echo "   Create it here: https://github.com/Grazulex/laravel-arc/releases/new?tag=v2.1.0"
fi
echo ""

# 5. Vérifier la qualité du code
echo "🧪 Code Quality Status:"
echo "Running composer quality..."
if composer quality > /dev/null 2>&1; then
    echo "✅ All quality checks pass"
else
    echo "❌ Quality checks have issues"
fi
echo ""

# 6. Instructions finales
echo "📋 Next Steps:"
echo "1. 🌐 Create GitHub Release: https://github.com/Grazulex/laravel-arc/releases/new?tag=v2.1.0"
echo "2. 📦 Packagist will auto-update via webhook (may take 5-10 minutes)"
echo "3. 📢 Optional: Announce on Laravel News, Reddit r/laravel, Twitter"
echo "4. 🔗 Optional: Update any documentation or tutorials"
echo ""
echo "✨ Release Content Ready in: RELEASE_NOTES_v2.1.0.md"
echo "🎉 Laravel Arc v2.1.0 Release Process Complete!"

