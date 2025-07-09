#!/bin/bash

# Script pour mettre à jour tous les fichiers de test avec describe()

# Liste des fichiers à traiter
files=(
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/IdFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/TimeFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/TextFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/EnumFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/DecimalFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/JsonFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/DateTimeFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/ArrayFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/UuidFieldGeneratorTest.php"
    "/home/jean-marc-strauven/Dev/laravel-arc/tests/Unit/Field/DateFieldGeneratorTest.php"
)

# Fonction pour traiter chaque fichier
process_file() {
    local file="$1"
    local classname=$(basename "$file" .php)
    
    # Extraire le nom de la classe du nom du fichier
    local describe_name=${classname}
    
    echo "Processing $file..."
    
    # Créer une sauvegarde
    cp "$file" "${file}.backup"
    
    # Utiliser sed pour transformer le fichier
    sed -i "s/^it(/describe('$describe_name', function () {\n    it(/g" "$file"
    sed -i 's/^});$/    });\n});/g' "$file"
    
    echo "Processed $file"
}

# Traiter tous les fichiers
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        process_file "$file"
    else
        echo "File not found: $file"
    fi
done

echo "All files processed!"
