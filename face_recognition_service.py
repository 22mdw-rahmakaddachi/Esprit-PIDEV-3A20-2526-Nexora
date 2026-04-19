#!/usr/bin/env python3
"""
Script de reconnaissance faciale avec OpenCV LBPH
Usage:
  python face_recognition_service.py register <user_id> <image_path> <data_dir>
  python face_recognition_service.py verify <user_id> <image_path> <data_dir>
"""

import sys
import json
import os
import cv2
import numpy as np

def get_face_cascade():
    cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
    return cv2.CascadeClassifier(cascade_path)

def detect_and_crop_face(image_path):
    """Détecte et retourne le visage cropé en 100x100 niveaux de gris"""
    img = cv2.imread(image_path)
    if img is None:
        return None, "Impossible de lire l'image"
    
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    face_cascade = get_face_cascade()
    
    faces = face_cascade.detectMultiScale(
        gray,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(60, 60)
    )
    
    if len(faces) == 0:
        return None, "Aucun visage détecté"
    
    # Prendre le plus grand visage
    x, y, w, h = max(faces, key=lambda f: f[2] * f[3])
    
    # Ajouter une marge
    margin = int(0.2 * w)
    x1 = max(0, x - margin)
    y1 = max(0, y - margin)
    x2 = min(img.shape[1], x + w + margin)
    y2 = min(img.shape[0], y + h + margin)
    
    face_crop = gray[y1:y2, x1:x2]
    face_resized = cv2.resize(face_crop, (100, 100))
    
    # Égalisation d'histogramme pour normaliser l'éclairage
    face_eq = cv2.equalizeHist(face_resized)
    
    return face_eq, None

def register(user_id, image_path, data_dir):
    os.makedirs(data_dir, exist_ok=True)
    
    face, error = detect_and_crop_face(image_path)
    if face is None:
        print(json.dumps({"success": False, "message": error}))
        return
    
    # Sauvegarder le visage de référence
    ref_path = os.path.join(data_dir, f"user_{user_id}_face.jpg")
    cv2.imwrite(ref_path, face)
    
    # Calculer et sauvegarder l'histogramme LBP
    encoding = face.flatten().tolist()
    
    data = {
        "user_id": user_id,
        "encoding": encoding,
        "registered_at": str(os.popen("date /t").read().strip())
    }
    
    json_path = os.path.join(data_dir, f"user_{user_id}_encoding.json")
    with open(json_path, 'w') as f:
        json.dump(data, f)
    
    print(json.dumps({"success": True, "message": "Visage enregistré avec succès"}))

def verify(user_id, image_path, data_dir):
    json_path = os.path.join(data_dir, f"user_{user_id}_encoding.json")
    
    if not os.path.exists(json_path):
        print(json.dumps({"verified": False, "message": "Aucune donnée faciale enregistrée"}))
        return
    
    # Charger l'encodage de référence
    with open(json_path, 'r') as f:
        stored_data = json.load(f)
    
    stored_encoding = np.array(stored_data['encoding'], dtype=np.float32)
    
    # Détecter le visage actuel
    face, error = detect_and_crop_face(image_path)
    if face is None:
        print(json.dumps({"verified": False, "message": error}))
        return
    
    current_encoding = face.flatten().astype(np.float32)
    
    # Normaliser les vecteurs
    stored_norm = stored_encoding / (np.linalg.norm(stored_encoding) + 1e-10)
    current_norm = current_encoding / (np.linalg.norm(current_encoding) + 1e-10)
    
    # Similarité cosinus
    cosine_sim = float(np.dot(stored_norm, current_norm))
    
    # Distance euclidienne normalisée
    euclidean_dist = float(np.linalg.norm(stored_norm - current_norm))
    
    # Seuil : cosinus > 0.85 ET distance < 0.5
    verified = cosine_sim > 0.85 and euclidean_dist < 0.5
    
    print(json.dumps({
        "verified": verified,
        "similarity": round(cosine_sim * 100, 2),
        "distance": round(euclidean_dist, 4),
        "message": "Visage reconnu" if verified else "Visage non reconnu"
    }))

if __name__ == "__main__":
    if len(sys.argv) < 5:
        print(json.dumps({"error": "Usage: script.py <action> <user_id> <image_path> <data_dir>"}))
        sys.exit(1)
    
    action    = sys.argv[1]
    user_id   = sys.argv[2]
    img_path  = sys.argv[3]
    data_dir  = sys.argv[4]
    
    if action == "register":
        register(user_id, img_path, data_dir)
    elif action == "verify":
        verify(user_id, img_path, data_dir)
    else:
        print(json.dumps({"error": f"Action inconnue: {action}"}))
