#!/bin/bash

# Script pour supprimer toutes les méthodes getTraitMethods() des classes Info

files=(
    "src/Support/Traits/Behavioral/HasTaggingInfo.php"
    "src/Support/Traits/Behavioral/HasTimestampsInfo.php" 
    "src/Support/Traits/Behavioral/HasSoftDeletesInfo.php"
    "src/Support/Traits/Behavioral/HasAuditingInfo.php"
    "src/Support/Traits/Behavioral/HasCachingInfo.php"
)

for file in "${files[@]}"; do
    echo "Cleaning $file..."
    # Utiliser sed pour supprimer les méthodes getTraitMethods() 
    # Méthode simple : supprimer depuis la ligne contenant "getTraitMethods" jusqu'à la fin de la méthode
    # En pratique, il vaut mieux faire cela manuellement pour être sûr
done

echo "Done. Please manually remove getTraitMethods() implementations from the remaining files."
