import json
import os
import sys
import time
import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Force UTF-8 output (fix Windows charmap error)
sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')

# =========================
# CONFIG
# =========================
options = Options()
options.add_argument("--start-maximized")

driver = webdriver.Chrome(options=options)

url = "https://www.tunisiepromo.tn/voyages-organises/liste"
driver.get(url)

wait = WebDriverWait(driver, 20)

# =========================
# SCROLL POUR CHARGER TOUTES LES OFFRES
# =========================
last_height = driver.execute_script("return document.body.scrollHeight")

while True:
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
    time.sleep(3)  # important pour laisser charger AJAX

    new_height = driver.execute_script("return document.body.scrollHeight")
    if new_height == last_height:
        break
    last_height = new_height

# =========================
# ATTENTE CARTES
# =========================
wait.until(EC.presence_of_all_elements_located((By.CSS_SELECTOR, ".item-wrap")))

cards = driver.find_elements(By.CSS_SELECTOR, ".item-wrap")

data = []

# =========================
# DOSSIER IMAGES
# =========================
image_folder = "images"
os.makedirs(image_folder, exist_ok=True)

# =========================
# SCRAPING
# =========================
for card in cards:

    try:
        titre = card.find_element(By.CSS_SELECTOR, ".property-title a").text.strip()
    except:
        titre = ""

    try:
        prix = card.find_element(By.CSS_SELECTOR, ".list-vo-item-price-edit").text.strip()
    except:
        prix = ""

    try:
        pays = card.find_element(By.CSS_SELECTOR, ".list-vo-item-pays-edit").text.strip()
    except:
        pays = ""

    try:
        duree = card.find_element(By.CSS_SELECTOR, ".list-vo-item-duree-edit").text.strip()
    except:
        duree = ""

    try:
        date = card.find_element(By.CSS_SELECTOR, ".list-vo-item-retour-edit").text.strip()
    except:
        date = ""

    try:
        description = card.find_element(By.CSS_SELECTOR, ".list-vo-item-description-edit").text.strip()
    except:
        description = ""

    try:
        lien = card.find_element(By.CSS_SELECTOR, ".property-title a").get_attribute("href")
    except:
        lien = ""

    try:
        image = card.find_element(By.CSS_SELECTOR, ".photo-voyage").get_attribute("src")
    except:
        image = ""

    # =========================
    # DOWNLOAD IMAGE
    # =========================
    image_local = ""

    if image and titre:
        try:
            filename = "".join(c for c in titre if c.isalnum() or c in " _-") + ".jpg"
            filepath = os.path.join(image_folder, filename)

            img_data = requests.get(image).content
            with open(filepath, "wb") as f:
                f.write(img_data)

            image_local = filepath
        except:
            image_local = ""

    # =========================
    # SAVE DATA
    # =========================
    if titre:
        data.append({
            "titre": titre,
            "prix": prix,
            "pays": pays,
            "duree": duree,
            "date": date,
            "description": description,
            "lien": lien,
            "image_url": image,
            "image_local": image_local
        })

# =========================
# SAVE JSON
# =========================
path = r"C:\Users\anoir\Downloads\Esprit-PIDEV-3A20-2526-Nexora-integration\public\offres.json"

with open(path, "w", encoding="utf-8") as f:
    json.dump(data, f, indent=4, ensure_ascii=False)

# =========================
# RESULT
# =========================
print("OK Total offres recuperees :", len(data))
print("JSON :", path)

driver.quit()