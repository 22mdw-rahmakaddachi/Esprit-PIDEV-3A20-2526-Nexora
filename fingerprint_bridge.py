#!/usr/bin/env python3
"""
Bridge Python entre Arduino (AS608) et Symfony
Lance avec : python fingerprint_bridge.py
"""

import serial
import serial.tools.list_ports
import threading
import time
from flask import Flask, jsonify, request

app = Flask(__name__)

BAUD_RATE    = 9600
ser          = None          # connexion serie globale
op_lock      = threading.Lock()  # lock pour les operations Arduino (enroll/verify)
enroll_step  = 0             # 0=idle 1=1ere capture 2=2eme capture

# ── Connexion Arduino ──
def connect():
    global ser
    ports = serial.tools.list_ports.comports()
    if not ports:
        return False
    port = ports[0].device
    try:
        ser = serial.Serial(port, BAUD_RATE, timeout=2)
        time.sleep(2)
        ser.reset_input_buffer()
        # Lire SENSOR_READY si present
        deadline = time.time() + 4
        while time.time() < deadline:
            if ser.in_waiting:
                line = ser.readline().decode('utf-8', errors='ignore').strip()
                print(f"[init] {line}")
                if 'READY' in line:
                    break
            time.sleep(0.1)
        print(f"Connecte sur {port}")
        return True
    except Exception as e:
        print(f"Erreur: {e}")
        ser = None
        return False

def readline_timeout(timeout=30):
    """Lire une ligne avec timeout"""
    deadline = time.time() + timeout
    while time.time() < deadline:
        if ser and ser.in_waiting:
            try:
                line = ser.readline().decode('utf-8', errors='ignore').strip()
                if line:
                    print(f"[Arduino] {line}")
                    return line
            except:
                return None
        time.sleep(0.05)
    return None

def wait_for(keywords, timeout=30):
    """Attendre un mot-cle specifique"""
    deadline = time.time() + timeout
    while time.time() < deadline:
        line = readline_timeout(timeout=min(1, deadline - time.time()))
        if line is None:
            continue
        for kw in keywords:
            if line.startswith(kw):
                return line
    return None

# ── Routes ──

@app.route('/status')
def status():
    ok = ser is not None and ser.is_open
    return jsonify({'connected': ok, 'port': ser.port if ok else None})

@app.route('/enroll/status')
def enroll_status():
    return jsonify({'step': enroll_step})

@app.route('/enroll', methods=['POST'])
def enroll():
    global enroll_step
    if not ser or not ser.is_open:
        return jsonify({'success': False, 'message': 'Lecteur non connecte'}), 503

    if not op_lock.acquire(timeout=2):
        return jsonify({'success': False, 'message': 'Operation en cours'}), 503

    enroll_step = 0
    try:
        ser.reset_input_buffer()
        ser.write(b'e')
        ser.flush()

        # 1. Attendre PLACE_FINGER
        enroll_step = 1
        line = wait_for(['PLACE_FINGER', 'ERROR'], timeout=5)
        if not line or line == 'ERROR':
            return jsonify({'success': False, 'message': 'Erreur capteur'})

        # 2. Attendre IMAGE_OK (1ere capture) — l'utilisateur pose le doigt
        line = wait_for(['IMAGE_OK', 'ERROR', 'FINGER_ALREADY_EXISTS:'], timeout=30)
        if not line:
            return jsonify({'success': False, 'message': 'Timeout — posez votre doigt'})
        if line == 'ERROR':
            return jsonify({'success': False, 'message': 'Erreur 1ere capture'})
        if line.startswith('FINGER_ALREADY_EXISTS:'):
            fid = int(line.split(':')[1])
            return jsonify({'success': False, 'already_exists': True, 'finger_id': fid,
                            'message': 'Ce doigt est deja enregistre'})

        # 3. Attendre PLACE_SAME_FINGER
        line = wait_for(['PLACE_SAME_FINGER', 'ERROR'], timeout=10)
        if not line or line == 'ERROR':
            return jsonify({'success': False, 'message': 'Erreur apres 1ere capture'})

        enroll_step = 2  # Signaler au JS de changer le message

        # 4. Attendre IMAGE_OK (2eme capture)
        line = wait_for(['IMAGE_OK', 'ERROR'], timeout=30)
        if not line or line == 'ERROR':
            return jsonify({'success': False, 'message': 'Erreur 2eme capture'})

        # 5. Attendre FINGER_ID
        line = wait_for(['FINGER_ID:', 'ERROR'], timeout=10)
        if not line or line == 'ERROR':
            return jsonify({'success': False, 'message': 'Erreur creation modele'})
        finger_id = int(line.split(':')[1])

        # 6. Attendre ENROLL_SUCCESS
        line = wait_for(['ENROLL_SUCCESS', 'ERROR'], timeout=5)
        if line == 'ENROLL_SUCCESS':
            return jsonify({'success': True, 'finger_id': finger_id,
                            'message': 'Empreinte enregistree avec succes'})
        return jsonify({'success': False, 'message': 'Erreur sauvegarde'})

    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500
    finally:
        enroll_step = 0
        op_lock.release()

@app.route('/verify', methods=['POST'])
def verify():
    if not ser or not ser.is_open:
        return jsonify({'authenticated': False, 'message': 'Lecteur non connecte'}), 503

    if not op_lock.acquire(timeout=2):
        return jsonify({'authenticated': False, 'message': 'Operation en cours'}), 503

    try:
        ser.reset_input_buffer()
        ser.write(b's')
        ser.flush()

        line = wait_for(['PLACE_FINGER', 'SENSOR_ERROR'], timeout=5)
        if not line or line == 'SENSOR_ERROR':
            return jsonify({'authenticated': False, 'message': 'Erreur capteur'})

        line = wait_for(['FINGER_ID:', 'ACCESS_DENIED', 'IMAGE_OK'], timeout=30)
        if not line:
            return jsonify({'authenticated': False, 'message': 'Timeout'})
        if line == 'ACCESS_DENIED':
            return jsonify({'authenticated': False, 'message': 'Empreinte non reconnue'})
        if line == 'IMAGE_OK':
            line = wait_for(['FINGER_ID:', 'ACCESS_DENIED'], timeout=5)
            if not line or line == 'ACCESS_DENIED':
                return jsonify({'authenticated': False, 'message': 'Empreinte non reconnue'})

        fid = int(line.split(':')[1])
        return jsonify({'authenticated': True, 'finger_id': fid})

    except Exception as e:
        return jsonify({'authenticated': False, 'message': str(e)}), 500
    finally:
        op_lock.release()

if __name__ == '__main__':
    print("=" * 50)
    print("  Bridge Fingerprint Arduino <-> Symfony")
    print("=" * 50)
    connect()
    print("Bridge sur http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=False, threaded=True)
