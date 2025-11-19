# app.py
from flask import Flask, request, jsonify
import joblib
import pandas as pd
from datetime import datetime

MODEL_PATH = "model.joblib"

app = Flask(__name__)
model = joblib.load(MODEL_PATH)

def featurize(payload):
    # payload: dict with suhu, kelembapan, hujan, waktu
    suhu = float(payload.get('suhu', 0))
    kelembapan = float(payload.get('kelembapan', 0))
    hujan = int(payload.get('hujan', 0))
    waktu_str = payload.get('waktu')
    if waktu_str:
        dt = pd.to_datetime(waktu_str)
    else:
        dt = pd.Timestamp.now()
    hour = dt.hour
    dow = dt.dayofweek
    last_rain = hujan
    return pd.DataFrame([{
        'suhu': suhu,
        'kelembapan': kelembapan,
        'hour': hour,
        'dow': dow,
        'last_rain': last_rain
    }])

@app.route("/", methods=["GET"])
def home():
    return jsonify({"message":"AI weather predictor. POST /predict with suhu, kelembapan, hujan, waktu"})

@app.route("/predict", methods=["POST"])
def predict():
    data = request.get_json() or {}
    try:
        X = featurize(data)
        prob = model.predict_proba(X)[0][1]  # prob of rain next hour
        pred = int(prob >= 0.5)
        # simple recommendation logic
        if prob >= 0.7:
            action = "TUTUP_JEMURAN"
        elif prob >= 0.4:
            action = "WASPADA"
        else:
            action = "AMAN"
        return jsonify({
            "success": True,
            "probability": round(float(prob), 3),
            "predicted_rain_next_hour": pred,
            "recommendation": action
        })
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 400

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
