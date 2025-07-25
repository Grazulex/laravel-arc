#!/usr/bin/env python3
import json
import requests
from datetime import datetime
import matplotlib.pyplot as plt
from pathlib import Path

# Configuration
VENDOR = "grazulex"
PACKAGE = "laravel-arc"
API_URL = f"https://packagist.org/packages/{VENDOR}/{PACKAGE}.json"
BASE_DIR = Path(__file__).resolve().parent.parent
STATS_DIR = BASE_DIR / "stats"
IMAGES_DIR = BASE_DIR / "images"
JSON_PATH = STATS_DIR / f"{PACKAGE}.json"
IMAGE_PATH = IMAGES_DIR / f"{PACKAGE}.png"

# Assurer que les dossiers existent
STATS_DIR.mkdir(parents=True, exist_ok=True)
IMAGES_DIR.mkdir(parents=True, exist_ok=True)

# Récupérer les stats depuis l'API Packagist
response = requests.get(API_URL)
if response.status_code != 200:
    print(f"Erreur: Impossible de récupérer les données depuis {API_URL}")
    exit(1)

data = response.json()
downloads = data["package"]["downloads"]
today = datetime.utcnow().strftime("%Y-%m-%d")

# Charger l'historique existant
if JSON_PATH.exists():
    with open(JSON_PATH, "r") as f:
        history = json.load(f)
else:
    history = {}

# Ajouter les stats du jour
history[today] = {
    "daily": downloads["daily"],
    "monthly": downloads["monthly"],
    "total": downloads["total"]
}

# Sauver l'historique mis à jour
with open(JSON_PATH, "w") as f:
    json.dump(history, f, indent=2)

# Générer le graphique
dates = list(history.keys())
totals = [history[date]["total"] for date in dates]

plt.figure(figsize=(10, 5))
plt.plot(dates, totals, marker="o", linestyle="-")
plt.title(f"📦 Total Downloads: {VENDOR}/{PACKAGE}")
plt.xlabel("Date")
plt.ylabel("Total Downloads")
plt.xticks(rotation=45)
plt.grid(True)
plt.tight_layout()
plt.savefig(IMAGE_PATH)

print(f"Graphique généré: {IMAGE_PATH}")
